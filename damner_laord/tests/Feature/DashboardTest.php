<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_dashboard_receives_the_overview_data()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/Overview')
                ->has('overview.kpis', 4)
                ->has('overview.bookingsTrend.series', 3)
                ->has('overview.attention')
                ->has('overview.latestBookings')
                ->where('overview.busTripType.total', 248)
            );
    }

    public function test_bookings_tab_receives_the_tracker_data()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard.bookings'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/Bookings')
                ->has('tracker.kpis', 5)
                ->has('tracker.funnel.steps', 6)
                ->missing('tracker.funnel.steps.0.note')
                ->where('tracker.funnel.steps.1.note', '75% of previous')
                ->has('tracker.activity')
                ->has('tracker.activeHolds')
            );
    }

    public function test_finance_tab_receives_the_tracker_data()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard.finance'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/Finance')
                ->has('tracker.kpis', 5)
                ->has('tracker.cashFlow.series', 2)
                ->has('tracker.moneyFlow.lines', 4)
                ->has('tracker.revenueByService', 3)
                ->has('tracker.settlements')
            );
    }

    public function test_trips_tab_receives_the_tracker_data()
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dashboard.trips'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/Trips')
                ->has('tracker.kpis', 5)
                ->has('tracker.alerts')
                ->has('tracker.departures')
            );
    }

    public function test_guests_cannot_open_any_dashboard_tab()
    {
        foreach (['dashboard.bookings', 'dashboard.finance', 'dashboard.trips'] as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }
}
