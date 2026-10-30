<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Notifications\MerchantInvited;

/**
 * "Invite merchant": an admin starts the application for a business. It is
 * created as Pending and the contact is emailed to finish the documents in
 * the merchant app, then it goes through the normal approval (spec 03.2).
 */
class InviteMerchantAction
{
    /**
     * @param  array<string, mixed>  $details  From InviteMerchantRequest::details()
     */
    public function execute(array $details): Merchant
    {
        $merchant = DB::transaction(function () use ($details): Merchant {
            $merchant = new Merchant($details);
            $merchant->save();

            return $merchant;
        });

        if ($merchant->email) {
            Notification::route('mail', $merchant->email)->notify(new MerchantInvited($merchant));
        }

        return $merchant;
    }
}
