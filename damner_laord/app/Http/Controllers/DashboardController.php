<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardOverviewService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(DashboardOverviewService $overview): Response
    {
        return Inertia::render('Dashboard', [
            'overview' => $overview->overview(),
        ]);
    }
}
