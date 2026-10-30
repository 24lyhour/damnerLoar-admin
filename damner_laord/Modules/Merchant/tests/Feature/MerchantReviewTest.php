<?php

namespace Modules\Merchant\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Enums\MerchantDocumentType;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Events\MerchantStatusChanged;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Notifications\MerchantChangesRequested;
use Tests\TestCase;

/** Spec 03.2 Merchant approval + merchant workflow (p.5). */
class MerchantReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['name' => 'Chantha Heng']);
    }

    public function test_detail_page_shows_checklist_with_missing_rows_and_permissions(): void
    {
        // Hotel needs 4 documents; only 2 exist (one verified).
        $merchant = Merchant::factory()->hotel()->pending()->create();
        $merchant->documents()->forceCreate(['type' => MerchantDocumentType::Contract, 'status' => MerchantDocumentStatus::Verified]);
        $merchant->documents()->forceCreate(['type' => MerchantDocumentType::BankAccount, 'status' => MerchantDocumentStatus::PendingReview, 'file_path' => 'x.pdf']);

        $this->actingAs($this->admin)
            ->get(route('merchant.merchants.show', $merchant))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('merchant::Dashboard/V1/Merchant/Show', false)
                ->where('merchant.uuid', $merchant->uuid)
                ->has('merchant.checklist', 4)
                ->where('merchant.checklist.0.status.value', 'missing')
                ->where('merchant.checklist.0.uuid', null)
                ->where('merchant.pendingDocumentCount', 3)
                ->where('merchant.can.approve', true)
                ->where('merchant.can.requestChanges', true)
                ->where('merchant.can.suspend', false));
    }

    public function test_approve_is_refused_until_every_document_is_verified(): void
    {
        $merchant = Merchant::factory()->pending()->withDocuments(MerchantDocumentStatus::PendingReview)->create();

        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.approve', $merchant))
            ->assertSessionHasErrors('status');

        $this->assertSame(MerchantStatus::Pending, $merchant->fresh()?->status);
    }

    public function test_approve_activates_records_the_approver_and_fires_the_event(): void
    {
        Event::fake([MerchantStatusChanged::class]);
        $merchant = Merchant::factory()->pending()->withDocuments()->create();

        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.approve', $merchant))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $merchant->refresh();
        $this->assertSame(MerchantStatus::Active, $merchant->status);
        $this->assertSame($this->admin->id, $merchant->approved_by);
        $this->assertNotNull($merchant->approved_at);

        Event::assertDispatched(MerchantStatusChanged::class, fn (MerchantStatusChanged $e) => $e->from === MerchantStatus::Pending
            && $e->to === MerchantStatus::Active
            && $e->actor->is($this->admin));
    }

    public function test_request_changes_emails_the_merchant_and_writes_a_note(): void
    {
        Notification::fake();
        $merchant = Merchant::factory()->pending()->create(['email' => 'ops@giantibis.com']);

        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.request-changes', $merchant), [
                'message' => 'Please upload the new operating licence.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(MerchantStatus::ChangesRequested, $merchant->fresh()?->status);
        $this->assertDatabaseHas('merchant_notes', [
            'merchant_id' => $merchant->id,
            'author_id' => $this->admin->id,
            'body' => 'Changes requested: Please upload the new operating licence.',
        ]);
        Notification::assertSentOnDemand(
            MerchantChangesRequested::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'ops@giantibis.com',
        );
    }

    public function test_request_changes_needs_a_message(): void
    {
        $merchant = Merchant::factory()->pending()->create();

        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.request-changes', $merchant), ['message' => 'short'])
            ->assertSessionHasErrors('message');
    }

    public function test_reject_is_final_and_needs_a_reason(): void
    {
        $merchant = Merchant::factory()->pending()->create();

        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.reject', $merchant), ['reason' => ''])
            ->assertSessionHasErrors('reason');

        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.reject', $merchant), ['reason' => 'Licence is not genuine'])
            ->assertSessionHasNoErrors();

        $this->assertSame(MerchantStatus::Rejected, $merchant->fresh()?->status);

        // Rejected → anything else is not allowed.
        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.approve', $merchant))
            ->assertSessionHasErrors('status');
    }

    public function test_suspend_and_reactivate_a_live_merchant(): void
    {
        $merchant = Merchant::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.suspend', $merchant), ['reason' => '3 no-show trips'])
            ->assertSessionHasNoErrors();

        $merchant->refresh();
        $this->assertSame(MerchantStatus::Suspended, $merchant->status);
        $this->assertSame('3 no-show trips', $merchant->suspended_reason);

        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.reactivate', $merchant))
            ->assertSessionHasNoErrors();

        $merchant->refresh();
        $this->assertSame(MerchantStatus::Active, $merchant->status);
        $this->assertNull($merchant->suspended_reason);
    }

    public function test_a_pending_application_cannot_be_suspended(): void
    {
        $merchant = Merchant::factory()->pending()->create();

        $this->actingAs($this->admin)
            ->post(route('merchant.merchants.suspend', $merchant), ['reason' => 'Not allowed here'])
            ->assertSessionHasErrors('status');
    }

    public function test_merchant_staff_cannot_review_merchants(): void
    {
        $merchant = Merchant::factory()->pending()->withDocuments()->create();
        $staff = User::factory()->create(['merchant_id' => $merchant->id]);

        $this->actingAs($staff)->post(route('merchant.merchants.approve', $merchant))->assertForbidden();
        $this->actingAs($staff)->get(route('merchant.merchants.show', $merchant))->assertForbidden();

        $this->assertSame(MerchantStatus::Pending, $merchant->fresh()?->status);
    }
}
