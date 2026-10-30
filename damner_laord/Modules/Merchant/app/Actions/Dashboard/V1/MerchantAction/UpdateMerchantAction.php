<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use Illuminate\Support\Facades\DB;
use Modules\Merchant\Models\Merchant;

/**
 * Saves company details and the commission rate. Status never changes here;
 * that goes through TransitionMerchantStatusAction.
 */
class UpdateMerchantAction
{
    /**
     * @param  array<string, mixed>  $details  From UpdateMerchantRequest::details()
     */
    public function execute(Merchant $merchant, array $details): Merchant
    {
        return DB::transaction(function () use ($merchant, $details): Merchant {
            $merchant->fill($details)->save();

            return $merchant;
        });
    }
}
