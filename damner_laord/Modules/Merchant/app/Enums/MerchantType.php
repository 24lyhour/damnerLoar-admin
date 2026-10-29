<?php

namespace Modules\Merchant\Enums;

use App\Concerns\EnumOptions;

/** Spec 03.1 "Type" column. */
enum MerchantType: string
{
    use EnumOptions;

    case BusOperator = 'bus_operator';
    case Hotel = 'hotel';

    public function label(): string
    {
        return match ($this) {
            self::BusOperator => 'Bus operator',
            self::Hotel => 'Hotel',
        };
    }

    public function labelKh(): string
    {
        return match ($this) {
            self::BusOperator => 'ប្រតិបត្តិករឡានក្រុង',
            self::Hotel => 'សណ្ឋាគារ',
        };
    }

    public function tone(): string
    {
        return 'neutral';
    }
}
