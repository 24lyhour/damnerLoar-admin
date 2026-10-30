<?php

namespace Modules\Merchant\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Enums\MerchantType;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Notifications\MerchantInvited;
use Tests\TestCase;

/**
 * InertiaUI modal routes (universe pattern): opening a modal URL directly
 * renders its base page with the modal on top.
 */
class MerchantModalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{base: string, modal: string, props: array<string, mixed>}
     */
    private function modalOf(TestResponse $response): array
    {
        $page = $response->viewData('page');

        return [
            'base' => $page['component'],
            'modal' => $page['props']['_inertiaui_modal']['component'],
            'props' => $page['props']['_inertiaui_modal']['props'],
        ];
    }

    public function test_edit_opens_as_a_modal_over_the_merchant_page(): void
    {
        $merchant = Merchant::factory()->create();

        $modal = $this->modalOf(
            $this->actingAs(User::factory()->create())
                ->get(route('merchant.merchants.edit', $merchant))
                ->assertOk(),
        );

        $this->assertSame('merchant::Dashboard/V1/Merchant/Show', $modal['base']);
        $this->assertSame('merchant::Dashboard/V1/Merchant/Edit', $modal['modal']);
        $this->assertSame($merchant->uuid, $modal['props']['merchant']['uuid']);
    }

    public function test_invite_opens_as_a_modal_over_the_list(): void
    {
        $modal = $this->modalOf(
            $this->actingAs(User::factory()->create())
                ->get(route('merchant.merchants.create'))
                ->assertOk(),
        );

        $this->assertSame('merchant::Dashboard/V1/Merchant/Index', $modal['base']);
        $this->assertSame('merchant::Dashboard/V1/Merchant/Invite', $modal['modal']);
        $this->assertCount(2, $modal['props']['types']);
    }

    public function test_status_confirm_modals_carry_the_review_name(): void
    {
        $merchant = Merchant::factory()->create();

        $modal = $this->modalOf(
            $this->actingAs(User::factory()->create())
                ->get(route('merchant.merchants.confirm-suspend', $merchant))
                ->assertOk(),
        );

        $this->assertSame('merchant::Dashboard/V1/Merchant/Review', $modal['modal']);
        $this->assertSame('suspend', $modal['props']['review']);
    }

    public function test_merchant_staff_cannot_open_admin_modals(): void
    {
        $merchant = Merchant::factory()->create();
        $staff = User::factory()->create(['merchant_id' => $merchant->id]);

        $this->actingAs($staff)->get(route('merchant.merchants.edit', $merchant))->assertForbidden();
        $this->actingAs($staff)->get(route('merchant.merchants.create'))->assertForbidden();
        $this->actingAs($staff)->get(route('merchant.merchants.confirm-suspend', $merchant))->assertForbidden();
    }

    public function test_invite_creates_a_pending_merchant_and_emails_the_contact(): void
    {
        Notification::fake();

        $this->actingAs(User::factory()->create())
            ->post(route('merchant.merchants.store'), [
                'type' => 'hotel',
                'name' => 'Kampot Riverside Lodge',
                'contact_name' => 'Dara',
                'email' => 'hello@kampotriverside.com',
                'city' => 'Kampot',
                'commission_percent' => '15',
            ])
            ->assertSessionHasNoErrors();

        $merchant = Merchant::query()->where('name', 'Kampot Riverside Lodge')->firstOrFail();

        $this->assertSame(MerchantStatus::Pending, $merchant->status);
        $this->assertSame(MerchantType::Hotel, $merchant->type);
        $this->assertSame(1500, $merchant->commission_rate);
        Notification::assertSentOnDemand(
            MerchantInvited::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'hello@kampotriverside.com',
        );
    }

    public function test_invite_needs_a_type_and_an_email(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('merchant.merchants.store'), ['name' => 'X', 'commission_percent' => '12'])
            ->assertSessionHasErrors(['type', 'email']);
    }
}
