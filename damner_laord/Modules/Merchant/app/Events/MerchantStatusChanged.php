<?php

namespace Modules\Merchant\Events;

use App\Models\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Models\Merchant;

/**
 * Fired after any merchant status change is committed.
 * Listeners: audit log (Admin), payouts on hold when suspended (Finance),
 * hide listings when not active (Transport, Hotel).
 */
class MerchantStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Merchant $merchant,
        public readonly MerchantStatus $from,
        public readonly MerchantStatus $to,
        public readonly User $actor,
        public readonly ?string $reason = null,
    ) {}
}
