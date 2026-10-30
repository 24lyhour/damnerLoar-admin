<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use App\Models\User;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Models\Merchant;

/**
 * Stops an active merchant selling. Finance puts their payouts on hold
 * when it hears MerchantStatusChanged (spec 09.5).
 */
class SuspendMerchantAction
{
    public function __construct(private readonly TransitionMerchantStatusAction $transition) {}

    public function execute(Merchant $merchant, User $actor, string $reason): Merchant
    {
        return $this->transition->execute($merchant, MerchantStatus::Suspended, $actor, $reason, [
            'suspended_reason' => $reason,
        ]);
    }
}
