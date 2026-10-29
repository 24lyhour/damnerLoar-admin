<?php

namespace Modules\Merchant\Enums;

use App\Concerns\EnumOptions;

/** Approval checklist items (spec 03.2). */
enum MerchantDocumentType: string
{
    use EnumOptions;

    case BusinessRegistration = 'business_registration';
    case TaxRegistration = 'tax_registration';
    case BankAccount = 'bank_account';
    case VehicleList = 'vehicle_list';
    case OperatingLicence = 'operating_licence';
    case Contract = 'contract';

    public function label(): string
    {
        return match ($this) {
            self::BusinessRegistration => 'Business registration',
            self::TaxRegistration => 'Tax registration (Patent)',
            self::BankAccount => 'Bank account for payouts',
            self::VehicleList => 'Vehicle list & seat layouts',
            self::OperatingLicence => 'Operating licence (Ministry of Public Works & Transport)',
            self::Contract => 'Contract signed',
        };
    }

    public function labelKh(): string
    {
        return match ($this) {
            self::BusinessRegistration => 'ការចុះបញ្ជីអាជីវកម្ម',
            self::TaxRegistration => 'ការចុះបញ្ជីពន្ធ (ប៉ាតង់)',
            self::BankAccount => 'គណនីធនាគារសម្រាប់ទូទាត់',
            self::VehicleList => 'បញ្ជីយានជំនិះ និងប្លង់កៅអី',
            self::OperatingLicence => 'អាជ្ញាប័ណ្ណប្រតិបត្តិការ',
            self::Contract => 'កិច្ចសន្យាបានចុះហត្ថលេខា',
        };
    }

    public function tone(): string
    {
        return 'neutral';
    }

    /**
     * Documents a merchant of this type must have verified before approval.
     *
     * @return list<self>
     */
    public static function requiredFor(MerchantType $type): array
    {
        $common = [self::BusinessRegistration, self::TaxRegistration, self::BankAccount, self::Contract];

        return $type === MerchantType::BusOperator
            ? [...$common, self::VehicleList, self::OperatingLicence]
            : $common;
    }
}
