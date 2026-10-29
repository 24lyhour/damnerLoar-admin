<?php

namespace Modules\Merchant\Models;

use App\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Merchant\Database\Factories\MerchantDocumentFactory;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Enums\MerchantDocumentType;

/**
 * One item on a merchant's approval checklist (spec 03.2).
 *
 * @property int $id
 * @property string $uuid
 * @property int $merchant_id
 * @property MerchantDocumentType $type
 * @property MerchantDocumentStatus $status
 * @property string|null $file_path
 * @property string|null $file_name
 * @property int|null $file_size
 * @property Carbon|null $expires_at
 * @property string|null $note
 * @property Carbon|null $verified_at
 * @property int|null $verified_by
 */
#[Fillable(['type', 'file_path', 'file_name', 'file_size', 'expires_at', 'note'])]
class MerchantDocument extends Model
{
    /** @use HasFactory<MerchantDocumentFactory> */
    use HasFactory, HasUuid;

    protected $attributes = [
        'status' => 'missing',
    ];

    protected function casts(): array
    {
        return [
            'type' => MerchantDocumentType::class,
            'status' => MerchantDocumentStatus::class,
            'file_size' => 'integer',
            'expires_at' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    protected static function newFactory(): MerchantDocumentFactory
    {
        return MerchantDocumentFactory::new();
    }

    /**
     * @return BelongsTo<Merchant, $this>
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
