<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Admin-only app: no public landing page. Guests are bounced on to /login.
Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::controller(DashboardController::class)->prefix('dashboard')->group(function () {
        Route::get('/', 'overview')->name('dashboard');
        Route::get('bookings', 'bookings')->name('dashboard.bookings');
        Route::get('finance', 'finance')->name('dashboard.finance');
        Route::get('trips', 'trips')->name('dashboard.trips');
    });
});

require __DIR__.'/settings.php';
