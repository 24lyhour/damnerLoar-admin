<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Models\Merchant;

/**
 * Spec 03.2 "Approve": only when every required document is verified.
 * The UI disables the button too, but this is the check that counts.
 */
class ApproveMerchantAction
{
    public function __construct(private readonly TransitionMerchantStatusAction $transition) {}

    public function execute(Merchant $merchant, User $actor): Merchant
    {
        $merchant->load('documents');

        if (! $merchant->isReadyForApproval()) {
            $left = $merchant->pendingDocumentCount();

            throw ValidationException::withMessages([
                'status' => "Verify every required document first ({$left} left).",
            ]);
        }

        return $this->transition->execute($merchant, MerchantStatus::Active, $actor, extra: [
            'approved_at' => now(),
            'approved_by' => $actor->id,
            'suspended_reason' => null,
        ]);
    }
}
