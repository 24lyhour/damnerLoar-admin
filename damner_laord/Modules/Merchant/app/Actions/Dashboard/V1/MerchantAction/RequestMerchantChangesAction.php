<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Modules\Merchant\Actions\Dashboard\V1\MerchantNoteAction\AddMerchantNoteAction;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Notifications\MerchantChangesRequested;

/**
 * Spec 03.2 "Request changes": emails the merchant what to fix and keeps a
 * copy in the internal notes.
 */
class RequestMerchantChangesAction
{
    public function __construct(
        private readonly TransitionMerchantStatusAction $transition,
        private readonly AddMerchantNoteAction $addNote,
    ) {}

    public function execute(Merchant $merchant, User $actor, string $message): Merchant
    {
        DB::transaction(function () use ($merchant, $actor, $message): void {
            $this->transition->execute($merchant, MerchantStatus::ChangesRequested, $actor, $message);
            $this->addNote->execute($merchant, $actor, "Changes requested: {$message}");
        });

        if ($merchant->email) {
            Notification::route('mail', $merchant->email)
                ->notify(new MerchantChangesRequested($merchant, $message));
        }

        return $merchant;
    }
}
