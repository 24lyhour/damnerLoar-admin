<?php

namespace Modules\Merchant\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Modules\Merchant\Models\Merchant;
use Tests\TestCase;

/** Spec 03.1 Merchant list. */
class MerchantListTest extends TestCase
{
    use RefreshDatabase;

    private const PAGE = 'merchant::Dashboard/V1/Merchant/Index';

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('merchant.merchants.index'))->assertRedirect(route('login'));
    }

    public function test_merchant_staff_cannot_open_the_admin_list(): void
    {
        $staff = User::factory()->create(['merchant_id' => Merchant::factory()->create()->id]);

        $this->actingAs($staff)->get(route('merchant.merchants.index'))->assertForbidden();
    }

    public function test_admin_sees_merchants_with_counts_and_filter_options(): void
    {
        Merchant::factory()->count(3)->create(['city' => 'Kampot']);
        Merchant::factory()->pending()->create(['city' => 'Siem Reap']);
        Merchant::factory()->suspended()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('merchant.merchants.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component(self::PAGE, false)
                ->has('merchants.data', 5)
                ->has('merchants.meta', fn (Assert $meta) => $meta->where('total', 5)->etc())
                ->where('counts.all', 5)
                ->where('counts.pending', 1)
                ->where('counts.suspended', 1)
                ->where('counts.active', 3)
                ->has('types', 2)
                ->where('cities', fn ($cities) => collect($cities)->contains('Kampot'))
                ->has('merchants.data.0', fn (Assert $row) => $row
                    ->hasAll(['uuid', 'name', 'initials', 'subtitle', 'type', 'city', 'commissionPercent', 'status', 'joinedAt'])
                    ->hasAll(['legalName', 'contactName', 'email', 'phone', 'website', 'address', 'province'])
                    ->has('can', 7)
                    ->where('status.tone', fn ($tone) => in_array($tone, ['success', 'neutral', 'error'], true))
                    ->etc()));
    }

    /** The row "⋯" menu uses the same permissions as the detail page. */
    public function test_each_row_carries_its_own_permissions(): void
    {
        $active = Merchant::factory()->create(['name' => 'A Active']);
        $suspended = Merchant::factory()->suspended()->create(['name' => 'B Suspended']);

        $rows = collect(
            $this->actingAs(User::factory()->create())
                ->get(route('merchant.merchants.index', ['sort' => 'name']))
                ->viewData('page')['props']['merchants']['data'],
        )->keyBy('uuid');

        $this->assertTrue($rows[$active->uuid]['can']['suspend']);
        $this->assertFalse($rows[$active->uuid]['can']['reactivate']);
        $this->assertFalse($rows[$suspended->uuid]['can']['suspend']);
        $this->assertTrue($rows[$suspended->uuid]['can']['reactivate']);
        $this->assertTrue($rows[$active->uuid]['can']['update']);
    }

    public function test_status_tab_type_city_and_search_filter_on_the_server(): void
    {
        Merchant::factory()->create(['name' => 'Capital Express', 'city' => 'Phnom Penh']);
        Merchant::factory()->hotel()->create(['name' => 'Angkor Paradise Hotel', 'city' => 'Siem Reap']);
        Merchant::factory()->pending()->create(['name' => 'Giant Ibis', 'city' => 'Phnom Penh']);

        $admin = User::factory()->create();

        $names = fn (array $query) => collect(
            $this->actingAs($admin)->get(route('merchant.merchants.index', $query))
                ->viewData('page')['props']['merchants']['data'],
        )->pluck('name')->sort()->values()->all();

        $this->assertSame(['Giant Ibis'], $names(['status' => 'pending']));
        $this->assertSame(['Angkor Paradise Hotel'], $names(['type' => 'hotel']));
        $this->assertSame(['Capital Express', 'Giant Ibis'], $names(['city' => 'Phnom Penh']));
        $this->assertSame(['Capital Express'], $names(['search' => 'capital']));
    }

    public function test_sorting_and_page_size(): void
    {
        Merchant::factory()->create(['name' => 'B Bus']);
        Merchant::factory()->create(['name' => 'A Bus']);
        Merchant::factory()->count(10)->create();

        $response = $this->actingAs(User::factory()->create())
            ->get(route('merchant.merchants.index', ['sort' => 'name', 'per_page' => 10]));

        $response->assertInertia(fn (Assert $page) => $page
            ->has('merchants.data', 10)
            ->where('merchants.meta.per_page', 10)
            ->where('merchants.meta.last_page', 2)
            ->where('filters.sort', 'name'));
    }

    /**
     * Regression: with no query string, filters must still be a JSON object.
     * An empty array became [] in Vue, so filters.sort was Array.prototype.sort.
     */
    public function test_filters_are_an_object_even_without_a_query_string(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('merchant.merchants.index'));

        $filters = json_decode(json_encode($response->viewData('page')['props']['filters']) ?: '', false);

        $this->assertIsObject($filters);
        $this->assertNull($filters->sort);
        $this->assertNull($filters->search);
        $this->assertSame(20, $filters->per_page);
    }

    public function test_unknown_sort_and_page_size_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('merchant.merchants.index', ['sort' => 'password', 'per_page' => 999]))
            ->assertSessionHasErrors(['sort', 'per_page']);
    }

    public function test_sidebar_badge_counts_applications_waiting_for_review(): void
    {
        Merchant::factory()->pending()->count(2)->create();
        Merchant::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('navBadges.merchants', 2));
    }
}
