<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Merchant\Actions\Dashboard\V1\MerchantNoteAction\AddMerchantNoteAction;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Models\Merchant;

/** Spec 03.2 "Reject": final; the application stays on record with the reason. */
class RejectMerchantAction
{
    public function __construct(
        private readonly TransitionMerchantStatusAction $transition,
        private readonly AddMerchantNoteAction $addNote,
    ) {}

    public function execute(Merchant $merchant, User $actor, string $reason): Merchant
    {
        return DB::transaction(function () use ($merchant, $actor, $reason): Merchant {
            $this->transition->execute($merchant, MerchantStatus::Rejected, $actor, $reason);
            $this->addNote->execute($merchant, $actor, "Application rejected: {$reason}");

            return $merchant;
        });
    }
}
