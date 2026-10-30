<?php

namespace Modules\Merchant\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Enums\MerchantDocumentType;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Enums\MerchantType;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Models\MerchantDocument;
use Modules\Merchant\Models\MerchantNote;

/**
 * Merchants from the spec screens (03.1 / 03.2) plus filler up to 42.
 * Safe to run more than once: named merchants are matched by name.
 */
class MerchantDatabaseSeeder extends Seeder
{
    private const TOTAL = 42;

    public function run(): void
    {
        $admin = User::query()->whereNull('merchant_id')->orderBy('id')->first()
            ?? User::factory()->create(['name' => 'Seed Admin']);

        $this->named('Capital Express', MerchantType::BusOperator, 'Phnom Penh', 'capital-express.com.kh', MerchantStatus::Active, $admin);
        $this->named('Sovan Rithy', MerchantType::BusOperator, 'Phnom Penh', 'sovanrithy.com', MerchantStatus::Active, $admin);
        $this->named('Ekareach Express', MerchantType::BusOperator, 'Kampot', 'ekareach.com', MerchantStatus::Active, $admin);
        $this->named('Angkor Paradise Hotel', MerchantType::Hotel, 'Siem Reap', null, MerchantStatus::Active, $admin);
        $this->named('Sokha Angkor Resort', MerchantType::Hotel, 'Siem Reap', null, MerchantStatus::Active, $admin);
        $this->named('Kampot Riverside Lodge', MerchantType::Hotel, 'Kampot', null, MerchantStatus::Pending, $admin);
        $this->named('Mekong Express', MerchantType::BusOperator, 'Phnom Penh', 'mekongexpress.com', MerchantStatus::Suspended, $admin)
            ->forceFill(['suspended_reason' => '3 no-show trips'])->save();

        $this->giantIbis($admin);

        $missing = self::TOTAL - Merchant::query()->count();

        if ($missing > 0) {
            Merchant::factory()->count($missing)->withDocuments()->create();
        }
    }

    private function named(
        string $name,
        MerchantType $type,
        string $city,
        ?string $website,
        MerchantStatus $status,
        User $admin,
    ): Merchant {
        $existing = Merchant::query()->where('name', $name)->first();

        if ($existing) {
            return $existing;
        }

        $factory = Merchant::factory();
        $factory = $type === MerchantType::Hotel ? $factory->hotel() : $factory;
        $factory = $status === MerchantStatus::Pending ? $factory->pending() : $factory->approvedBy($admin);

        return $factory->withDocuments()->create([
            'name' => $name,
            'city' => $city,
            'province' => $city,
            'website' => $website,
            'status' => $status,
        ]);
    }

    /** The spec 03.2 example: 4 verified, seat layouts missing, licence expired. */
    private function giantIbis(User $admin): void
    {
        if (Merchant::query()->where('name', 'Giant Ibis')->exists()) {
            return;
        }

        $merchant = Merchant::factory()->pending()->create([
            'name' => 'Giant Ibis',
            'legal_name' => 'Giant Ibis Transport Co., Ltd.',
            'contact_name' => 'Sreymom Keo',
            'email' => 'ops@giantibis.com',
            'phone' => '+855 23 999 333',
            'website' => 'giantibis.com',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
        ]);

        $notes = [
            MerchantDocumentType::BusinessRegistration->value => 'MOC certificate',
            MerchantDocumentType::TaxRegistration->value => 'GDT patent',
            MerchantDocumentType::BankAccount->value => 'ABA · account ending 3301 · name matches',
            MerchantDocumentType::Contract->value => 'Commission 12 % · payout weekly',
        ];

        foreach (MerchantDocumentType::requiredFor($merchant->type) as $type) {
            $document = MerchantDocument::factory()->for($merchant)->ofType($type);

            $document = match ($type) {
                MerchantDocumentType::VehicleList => $document->withStatus(MerchantDocumentStatus::PendingReview)
                    ->state(['note' => '6 vehicles uploaded · 2 missing seat layout']),
                MerchantDocumentType::OperatingLicence => $document->expired(),
                default => $document->verified()->state(['note' => $notes[$type->value], 'verified_by' => $admin->id]),
            };

            $document->create();
        }

        $note = new MerchantNote(['body' => 'Called Sreymom. New licence expected Monday. Seat layouts for 2 sleepers coming.']);
        $note->merchant()->associate($merchant);
        $note->author()->associate($admin);
        $note->save();
    }
}
