<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use App\Models\User;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Models\Merchant;

/** Lifts a suspension; the merchant sells again. */
class ReactivateMerchantAction
{
    public function __construct(private readonly TransitionMerchantStatusAction $transition) {}

    public function execute(Merchant $merchant, User $actor): Merchant
    {
        return $this->transition->execute($merchant, MerchantStatus::Active, $actor, extra: [
            'suspended_reason' => null,
        ]);
    }
}
