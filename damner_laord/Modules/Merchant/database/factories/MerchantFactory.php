<?php

namespace Modules\Merchant\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Enums\MerchantDocumentType;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Enums\MerchantType;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Models\MerchantDocument;

/**
 * @extends Factory<Merchant>
 */
class MerchantFactory extends Factory
{
    protected $model = Merchant::class;

    private const PROVINCES = ['Phnom Penh', 'Siem Reap', 'Kampot', 'Battambang', 'Sihanoukville', 'Koh Kong'];

    /**
     * An approved, selling bus operator by default.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $province = fake()->randomElement(self::PROVINCES);

        return [
            'type' => MerchantType::BusOperator,
            'name' => fake()->unique()->company(),
            'legal_name' => fake()->company().' Co., Ltd.',
            'contact_name' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => '+855 '.fake()->numerify('## ### ###'),
            'website' => fake()->optional()->domainName(),
            'address' => fake()->streetAddress(),
            'city' => $province,
            'province' => $province,
            'commission_rate' => 1200,
            'status' => MerchantStatus::Active,
            'approved_at' => now()->subMonths(2),
        ];
    }

    public function hotel(): static
    {
        return $this->state(['type' => MerchantType::Hotel, 'commission_rate' => 1500]);
    }

    /** A new application waiting for review. */
    public function pending(): static
    {
        return $this->state(['status' => MerchantStatus::Pending, 'approved_at' => null, 'approved_by' => null]);
    }

    public function suspended(string $reason = '3 no-show trips'): static
    {
        return $this->state(['status' => MerchantStatus::Suspended, 'suspended_reason' => $reason]);
    }

    public function approvedBy(User $admin): static
    {
        return $this->state(['approved_by' => $admin->id, 'approved_at' => now()]);
    }

    /**
     * Create every required checklist document, all with one status.
     */
    public function withDocuments(MerchantDocumentStatus $status = MerchantDocumentStatus::Verified): static
    {
        return $this->afterCreating(function (Merchant $merchant) use ($status): void {
            foreach (MerchantDocumentType::requiredFor($merchant->type) as $type) {
                MerchantDocument::factory()
                    ->for($merchant)
                    ->ofType($type)
                    ->withStatus($status)
                    ->create();
            }
        });
    }
}
