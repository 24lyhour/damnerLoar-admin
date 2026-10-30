<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantAction;

use Modules\Merchant\Enums\MerchantType;

/** Props for the Invite merchant modal. */
class GetMerchantCreateDataAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        return [
            'types' => MerchantType::options(),
            // Platform defaults (spec 11.1) until the Setting module owns them
            'defaultCommission' => [
                MerchantType::BusOperator->value => 12,
                MerchantType::Hotel->value => 15,
            ],
        ];
    }
}
