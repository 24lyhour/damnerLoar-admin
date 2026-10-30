<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use Modules\Merchant\Http\Resources\MerchantDetailResource;
use Modules\Merchant\Models\Merchant;

/** Props for the merchant approval / detail page (spec 03.2). */
class GetMerchantShowDataAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(Merchant $merchant): array
    {
        $merchant->load(['documents', 'notes.author', 'approvedBy']);

        return [
            'merchant' => (new MerchantDetailResource($merchant))->resolve(),
        ];
    }
}
