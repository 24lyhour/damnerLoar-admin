<?php

namespace Modules\Merchant\Http\Requests\Dashboard\V1\MerchantRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Merchant\Actions\Dashboard\V1\MerchantAction\GetMerchantIndexDataAction;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Enums\MerchantType;
use Modules\Merchant\Models\Merchant;

/**
 * Query string of the merchant list (sent by useTableQuery on the Vue side).
 *
 * @phpstan-import-type MerchantFilters from GetMerchantIndexDataAction
 */
class IndexMerchantRequest extends FormRequest
{
    public const PER_PAGE = [10, 20, 50];

    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Merchant::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $sorts = collect(array_keys(GetMerchantIndexDataAction::SORTABLE))
            ->flatMap(fn (string $key): array => [$key, "-{$key}"])
            ->all();

        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(MerchantStatus::class)],
            'type' => ['nullable', Rule::enum(MerchantType::class)],
            'city' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in($sorts)],
            'per_page' => ['nullable', 'integer', Rule::in(self::PER_PAGE)],
        ];
    }

    /**
     * @return MerchantFilters
     */
    public function filters(): array
    {
        return [
            'search' => $this->string('search')->trim()->value() ?: null,
            'status' => $this->input('status'),
            'type' => $this->input('type'),
            'city' => $this->input('city'),
            'sort' => $this->input('sort'),
            'per_page' => $this->integer('per_page', 20),
        ];
    }
}
