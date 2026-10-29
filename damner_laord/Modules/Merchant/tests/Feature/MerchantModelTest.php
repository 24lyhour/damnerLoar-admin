<?php

namespace Modules\Merchant\Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Merchant\Enums\MerchantDocumentStatus;
use Modules\Merchant\Enums\MerchantDocumentType;
use Modules\Merchant\Enums\MerchantStatus;
use Modules\Merchant\Enums\MerchantType;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Models\MerchantDocument;
use Tests\TestCase;

class MerchantModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_merchant_gets_a_uuid_and_starts_pending(): void
    {
        $merchant = Merchant::create([
            'type' => MerchantType::Hotel,
            'name' => 'Kampot Riverside Lodge',
            'commission_rate' => 1500,
        ])->refresh();

        $this->assertTrue(str($merchant->uuid)->isUuid());
        $this->assertSame('uuid', $merchant->getRouteKeyName());
        $this->assertSame(MerchantStatus::Pending, $merchant->status);
        $this->assertSame(MerchantType::Hotel, $merchant->type);
    }

    public function test_status_and_approval_fields_cannot_be_mass_assigned(): void
    {
        $merchant = Merchant::create([
            'type' => MerchantType::BusOperator,
            'name' => 'Giant Ibis',
            'commission_rate' => 1200,
            'status' => MerchantStatus::Active,
            'approved_at' => now(),
        ])->refresh();

        $this->assertSame(MerchantStatus::Pending, $merchant->status);
        $this->assertNull($merchant->approved_at);
    }

    public function test_merchant_has_staff_documents_and_an_approver(): void
    {
        $admin = User::factory()->create();
        $merchant = Merchant::factory()->approvedBy($admin)->withDocuments()->create();
        User::factory()->count(2)->create(['merchant_id' => $merchant->id]);

        $this->assertCount(2, $merchant->staff);
        $this->assertCount(6, $merchant->documents);
        $this->assertTrue($merchant->approvedBy->is($admin));
        $this->assertTrue($merchant->documents->first()->merchant->is($merchant));
    }

    public function test_required_documents_depend_on_merchant_type(): void
    {
        $this->assertCount(6, MerchantDocumentType::requiredFor(MerchantType::BusOperator));
        $this->assertCount(4, MerchantDocumentType::requiredFor(MerchantType::Hotel));
        $this->assertNotContains(
            MerchantDocumentType::VehicleList,
            MerchantDocumentType::requiredFor(MerchantType::Hotel),
        );
    }

    public function test_ready_for_approval_only_when_every_required_document_is_verified(): void
    {
        $merchant = Merchant::factory()->pending()->withDocuments()->create();

        $this->assertTrue($merchant->isReadyForApproval());

        // Spec 03.2 example: seat layouts missing, licence expired → "2 items left".
        $merchant->documents()->where('type', MerchantDocumentType::VehicleList)
            ->update(['status' => MerchantDocumentStatus::Missing]);
        $merchant->documents()->where('type', MerchantDocumentType::OperatingLicence)
            ->update(['status' => MerchantDocumentStatus::Expired]);

        $merchant->load('documents');

        $this->assertFalse($merchant->isReadyForApproval());
        $this->assertSame(2, $merchant->pendingDocumentCount());
    }

    public function test_a_merchant_has_one_checklist_row_per_document_type(): void
    {
        $merchant = Merchant::factory()->create();
        MerchantDocument::factory()->for($merchant)->ofType(MerchantDocumentType::Contract)->create();

        $this->expectException(QueryException::class);

        MerchantDocument::factory()->for($merchant)->ofType(MerchantDocumentType::Contract)->create();
    }

    public function test_deleting_a_merchant_is_soft(): void
    {
        $merchant = Merchant::factory()->create();

        $merchant->delete();

        $this->assertSoftDeleted($merchant);
    }

    public function test_enum_options_feed_filters_and_badges(): void
    {
        $active = collect(MerchantStatus::options())->firstWhere('value', 'active');

        $this->assertSame(
            ['value' => 'active', 'label' => 'Active', 'labelKh' => 'សកម្ម', 'tone' => 'success'],
            $active,
        );
        $this->assertSame('neutral', MerchantStatus::Pending->tone());
        $this->assertTrue(MerchantStatus::Active->canSell());
        $this->assertFalse(MerchantStatus::Suspended->canSell());
    }
}
