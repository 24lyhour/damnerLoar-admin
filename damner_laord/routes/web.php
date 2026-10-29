<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Admin-only app: no public landing page. Guests are bounced on to /login.
Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';
