<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use Illuminate\Database\Eloquent\Builder;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Enums\MerchantType;
use Modules\Merchant\Http\Resources\MerchantListResource;
use Modules\Merchant\Models\Merchant;

/**
 * Props for the merchant list (spec 03.1): the page of rows plus tab counts
 * and filter options. Search, filters, sort and paging run in the database.
 *
 * @phpstan-type MerchantFilters array{search: ?string, status: ?string, type: ?string, city: ?string, sort: ?string, per_page: int}
 */
class GetMerchantIndexDataAction
{
    /** Sort keys the table may send, mapped to columns. */
    public const SORTABLE = [
        'name' => 'name',
        'created_at' => 'created_at',
        'commission_rate' => 'commission_rate',
    ];

    private const DEFAULT_SORT = '-created_at';

    /**
     * @param  MerchantFilters  $filters
     * @return array<string, mixed>
     */
    public function execute(array $filters): array
    {
        $query = Merchant::query();

        $this->search($query, $filters['search']);

        $query
            ->when($filters['status'], fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['type'], fn (Builder $q, string $type) => $q->where('type', $type))
            ->when($filters['city'], fn (Builder $q, string $city) => $q->where('city', $city));

        $this->sort($query, $filters['sort'] ?? self::DEFAULT_SORT);

        return [
            'merchants' => MerchantListResource::collection($query->paginate($filters['per_page'])->withQueryString()),
            // Every key always present: an empty array would reach Vue as [] instead of {}
            'filters' => $filters,
            'counts' => $this->countsByStatus(),
            'cities' => $this->cities(),
            'types' => MerchantType::options(),
        ];
    }

    /**
     * @param  Builder<Merchant>  $query
     */
    private function search(Builder $query, ?string $term): void
    {
        if ($term === null || trim($term) === '') {
            return;
        }

        $like = '%'.trim($term).'%';

        $query->where(fn (Builder $q) => $q
            ->where('name', 'like', $like)
            ->orWhere('legal_name', 'like', $like)
            ->orWhere('email', 'like', $like)
            ->orWhere('phone', 'like', $like));
    }

    /**
     * "-created_at" → created_at desc. Unknown keys fall back to newest first.
     *
     * @param  Builder<Merchant>  $query
     */
    private function sort(Builder $query, string $sort): void
    {
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = self::SORTABLE[ltrim($sort, '-')] ?? 'created_at';

        $query->orderBy($column, $direction)->orderBy('id', $direction);
    }

    /**
     * @return array<string, int> "all" plus one entry per status
     */
    private function countsByStatus(): array
    {
        /** @var array<string, int> $counts */
        $counts = Merchant::query()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();

        $byStatus = [];

        foreach (MerchantStatus::cases() as $status) {
            $byStatus[$status->value] = $counts[$status->value] ?? 0;
        }

        return ['all' => array_sum($byStatus), ...$byStatus];
    }

    /**
     * @return list<string>
     */
    private function cities(): array
    {
        return array_values(Merchant::query()
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->get(['city'])
            ->map(fn (Merchant $merchant): ?string => $merchant->city)
            ->filter()
            ->all());
    }
}
