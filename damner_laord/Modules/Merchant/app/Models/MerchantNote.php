<?php

namespace Modules\Merchant\Models;

use App\Concerns\HasUuid;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Merchant\Database\Factories\MerchantNoteFactory;

/**
 * Internal admin note on a merchant (spec 03.2), e.g.
 * "Called Sreymom. New licence expected Monday."
 *
 * @property int $id
 * @property string $uuid
 * @property int $merchant_id
 * @property int $author_id
 * @property string $body
 * @property Carbon|null $created_at
 */
#[Fillable(['body'])]
class MerchantNote extends Model
{
    /** @use HasFactory<MerchantNoteFactory> */
    use HasFactory, HasUuid;

    protected static function newFactory(): MerchantNoteFactory
    {
        return MerchantNoteFactory::new();
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
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
