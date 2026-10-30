<?php

namespace Modules\Merchant\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Models\Merchant;
use Tests\TestCase;

/** "Edit details" modal on the merchant page. */
class MerchantUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'name' => 'Giant Ibis Express',
            'legal_name' => 'Giant Ibis Transport Co., Ltd.',
            'contact_name' => 'Sreymom Keo',
            'email' => 'ops@giantibis.com',
            'phone' => '+855 23 999 333',
            'website' => 'giantibis.com',
            'address' => 'St. 106, Phnom Penh',
            'city' => 'Phnom Penh',
            'province' => 'Phnom Penh',
            'commission_percent' => '12.5',
            ...$overrides,
        ];
    }

    public function test_admin_updates_details_and_commission_is_stored_in_basis_points(): void
    {
        $merchant = Merchant::factory()->pending()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('merchant.merchants.update', $merchant), $this->payload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $merchant->refresh();
        $this->assertSame('Giant Ibis Express', $merchant->name);
        $this->assertSame('ops@giantibis.com', $merchant->email);
        $this->assertSame(1250, $merchant->commission_rate);
    }

    public function test_status_cannot_be_changed_through_the_edit_form(): void
    {
        $merchant = Merchant::factory()->pending()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('merchant.merchants.update', $merchant), $this->payload(['status' => 'active']))
            ->assertSessionHasNoErrors();

        $this->assertSame(MerchantStatus::Pending, $merchant->fresh()?->status);
    }

    public function test_fields_are_validated(): void
    {
        $merchant = Merchant::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('merchant.merchants.update', $merchant), $this->payload([
                'name' => '',
                'email' => 'not-an-email',
                'commission_percent' => '75',
            ]))
            ->assertSessionHasErrors(['name', 'email', 'commission_percent']);

        $this->actingAs(User::factory()->create())
            ->patch(route('merchant.merchants.update', $merchant), $this->payload(['commission_percent' => '12.345']))
            ->assertSessionHasErrors('commission_percent');
    }

    public function test_merchant_staff_cannot_edit_merchants(): void
    {
        $merchant = Merchant::factory()->create();
        $staff = User::factory()->create(['merchant_id' => $merchant->id]);

        $this->actingAs($staff)
            ->patch(route('merchant.merchants.update', $merchant), $this->payload())
            ->assertForbidden();
    }
}
