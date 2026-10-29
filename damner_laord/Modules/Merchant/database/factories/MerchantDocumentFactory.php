<?php

namespace Modules\Merchant\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Enums\MerchantDocumentType;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Models\MerchantDocument;

/**
 * @extends Factory<MerchantDocument>
 */
class MerchantDocumentFactory extends Factory
{
    protected $model = MerchantDocument::class;

    /**
     * An uploaded document waiting for review.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(MerchantDocumentType::cases());

        return [
            'merchant_id' => Merchant::factory(),
            'type' => $type,
            'status' => MerchantDocumentStatus::PendingReview,
            'file_path' => 'merchants/documents/'.fake()->uuid().'.pdf',
            'file_name' => $type->value.'.pdf',
            'file_size' => fake()->numberBetween(200_000, 2_000_000),
        ];
    }

    public function ofType(MerchantDocumentType $type): static
    {
        return $this->state(['type' => $type, 'file_name' => $type->value.'.pdf']);
    }

    public function withStatus(MerchantDocumentStatus $status): static
    {
        return match ($status) {
            MerchantDocumentStatus::Missing => $this->missing(),
            MerchantDocumentStatus::Verified => $this->verified(),
            MerchantDocumentStatus::Expired => $this->expired(),
            default => $this->state(['status' => $status]),
        };
    }

    public function missing(): static
    {
        return $this->state([
            'status' => MerchantDocumentStatus::Missing,
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
        ]);
    }

    public function verified(): static
    {
        return $this->state(['status' => MerchantDocumentStatus::Verified, 'verified_at' => now()]);
    }

    public function expired(): static
    {
        return $this->state(['status' => MerchantDocumentStatus::Expired, 'expires_at' => now()->subMonth()]);
    }
}
