<?php

namespace Modules\Merchant\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Models\MerchantNote;

/**
 * @extends Factory<MerchantNote>
 */
class MerchantNoteFactory extends Factory
{
    protected $model = MerchantNote::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'merchant_id' => Merchant::factory(),
            'author_id' => User::factory(),
            'body' => fake()->sentence(12),
        ];
    }
}
