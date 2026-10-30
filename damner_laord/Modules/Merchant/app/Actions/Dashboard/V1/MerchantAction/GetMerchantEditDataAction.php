<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use Modules\Merchant\Http\Resources\MerchantListResource;
use Modules\Merchant\Models\Merchant;

/**
 * Props for the Edit modal and the status confirm modals: the merchant as a
 * list row (it carries the editable fields and `can`).
 */
class GetMerchantEditDataAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(Merchant $merchant): array
    {
        $merchant->load('documents');

        return [
            'merchant' => (new MerchantListResource($merchant))->resolve(),
            'pendingDocumentCount' => $merchant->pendingDocumentCount(),
        ];
    }
}
