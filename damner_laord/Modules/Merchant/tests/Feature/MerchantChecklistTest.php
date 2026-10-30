<?php

namespace Modules\Merchant\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Models\MerchantDocument;
use Tests\TestCase;

/** Spec 03.2 checklist ticks and internal notes. */
class MerchantChecklistTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_verifies_and_unverifies_an_uploaded_document(): void
    {
        $admin = User::factory()->create();
        $document = MerchantDocument::factory()->create();

        $route = route('merchant.merchants.documents.verify', [$document->merchant, $document]);

        $this->actingAs($admin)->patch($route, ['verified' => true])->assertSessionHasNoErrors();

        $document->refresh();
        $this->assertSame(MerchantDocumentStatus::Verified, $document->status);
        $this->assertSame($admin->id, $document->verified_by);

        $this->actingAs($admin)->patch($route, ['verified' => false])->assertSessionHasNoErrors();

        $document->refresh();
        $this->assertSame(MerchantDocumentStatus::PendingReview, $document->status);
        $this->assertNull($document->verified_by);
    }

    public function test_missing_or_expired_documents_cannot_be_verified(): void
    {
        $admin = User::factory()->create();
        $missing = MerchantDocument::factory()->missing()->create();
        $expired = MerchantDocument::factory()->expired()->create();

        foreach ([$missing, $expired] as $document) {
            $this->actingAs($admin)
                ->patch(route('merchant.merchants.documents.verify', [$document->merchant, $document]), ['verified' => true])
                ->assertSessionHasErrors('document');

            $this->assertNotSame(MerchantDocumentStatus::Verified, $document->fresh()?->status);
        }
    }

    public function test_a_document_can_only_be_changed_through_its_own_merchant(): void
    {
        $document = MerchantDocument::factory()->create();
        $otherMerchant = Merchant::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('merchant.merchants.documents.verify', [$otherMerchant, $document]), ['verified' => true])
            ->assertNotFound();
    }

    public function test_admin_adds_an_internal_note(): void
    {
        $admin = User::factory()->create();
        $merchant = Merchant::factory()->create();

        $this->actingAs($admin)
            ->post(route('merchant.merchants.notes.store', $merchant), ['body' => '  New licence expected Monday.  '])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('merchant_notes', [
            'merchant_id' => $merchant->id,
            'author_id' => $admin->id,
            'body' => 'New licence expected Monday.',
        ]);
    }
}
