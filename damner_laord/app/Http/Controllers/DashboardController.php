<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\BookingsTrackerService;
use App\Services\Dashboard\DashboardOverviewService;
use App\Services\Dashboard\FinanceTrackerService;
use App\Services\Dashboard\TripsTrackerService;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard: one sidebar item, one action per tab (spec 02.1 – 02.4).
 */
class DashboardController extends Controller
{
    public function overview(DashboardOverviewService $service): Response
    {
        return Inertia::render('dashboard/Overview', [
            'overview' => $service->overview(),
        ]);
    }

    public function bookings(BookingsTrackerService $service): Response
    {
        return Inertia::render('dashboard/Bookings', [
            'tracker' => $service->tracker(),
        ]);
    }

    public function finance(FinanceTrackerService $service): Response
    {
        return Inertia::render('dashboard/Finance', [
            'tracker' => $service->tracker(),
        ]);
    }

    public function trips(TripsTrackerService $service): Response
    {
        return Inertia::render('dashboard/Trips', [
            'tracker' => $service->tracker(),
        ]);
    }
}
