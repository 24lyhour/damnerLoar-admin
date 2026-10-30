<?php

namespace Modules\Merchant\Models;

use App\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Merchant\Database\Factories\MerchantFactory;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Enums\MerchantDocumentType;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Enums\MerchantType;

/**
 * A bus operator or hotel selling on DamnerLaor (spec 03).
 *
 * Hotels, vehicles and routes point here with `merchant_id` from their own
 * modules (Hotel, Transport); this model doesn't know about them, so the
 * Merchant module never depends on modules that depend on it.
 *
 * status / approved_* are not fillable: they change only through Actions
 * (approve, request changes, suspend) so every change is checked and audited.
 *
 * @property int $id
 * @property string $uuid
 * @property MerchantType $type
 * @property string $name
 * @property string|null $legal_name
 * @property string|null $contact_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $address
 * @property string|null $city
 * @property string|null $province
 * @property string|null $logo
 * @property int $commission_rate Basis points: 1200 = 12 %
 * @property MerchantStatus $status
 * @property string|null $suspended_reason
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'type', 'name', 'legal_name', 'contact_name', 'email', 'phone', 'website',
    'address', 'city', 'province', 'logo', 'commission_rate',
])]
class Merchant extends Model
{
    /** @use HasFactory<MerchantFactory> */
    use HasFactory, HasUuid, SoftDeletes;

    protected $attributes = [
        'status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'type' => MerchantType::class,
            'status' => MerchantStatus::class,
            'commission_rate' => 'integer',
            'approved_at' => 'datetime',
        ];
    }

    protected static function newFactory(): MerchantFactory
    {
        return MerchantFactory::new();
    }

    /**
     * Staff accounts that use the merchant app (users.merchant_id).
     *
     * @return HasMany<User, $this>
     */
    public function staff(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Approval checklist (spec 03.2).
     *
     * @return HasMany<MerchantDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(MerchantDocument::class);
    }

    /**
     * Internal admin notes, newest first (spec 03.2).
     *
     * @return HasMany<MerchantNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(MerchantNote::class)->latest();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Applications waiting for review: the sidebar badge "Merchants 3". */
    public static function awaitingReviewCount(): int
    {
        return static::query()
            ->whereIn('status', [MerchantStatus::Pending, MerchantStatus::ChangesRequested])
            ->count();
    }

    /**
     * Spec 03.2: "Approve stays disabled until every item is verified".
     * The approve Action must check this again on the server.
     */
    public function isReadyForApproval(): bool
    {
        return $this->pendingDocumentCount() === 0;
    }

    /**
     * Required documents not verified yet ("Approve · 2 items left").
     */
    public function pendingDocumentCount(): int
    {
        $verified = $this->documents
            ->where('status', MerchantDocumentStatus::Verified)
            ->pluck('type');

        return collect(MerchantDocumentType::requiredFor($this->type))
            ->reject(fn (MerchantDocumentType $type): bool => $verified->contains($type))
            ->count();
    }
}
