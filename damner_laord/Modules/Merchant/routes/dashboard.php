<?php

use Illuminate\Support\Facades\Route;
use Modules\Merchant\Http\Controllers\Dashboard\V1\MerchantController;
use Modules\Merchant\Http\Controllers\Dashboard\V1\MerchantDocumentController;
use Modules\Merchant\Http\Controllers\Dashboard\V1\MerchantNoteController;
use Modules\Merchant\Http\Controllers\Dashboard\V1\MerchantReviewController;

Route::middleware(['auth', 'verified'])
    ->prefix('dashboard')
    ->group(function () {

        // ---------------------------------------------------------------
        // Merchants (spec 03.1 list, 03.2 approval)
        // Scoped bindings: a {document} must belong to the {merchant}.
        // ---------------------------------------------------------------
        Route::prefix('merchants')
            ->name('merchant.merchants.')
            ->scopeBindings()
            ->group(function () {
                Route::get('/', [MerchantController::class, 'index'])->name('index');
                Route::get('invite', [MerchantController::class, 'create'])->name('create');
                Route::post('/', [MerchantController::class, 'store'])->name('store');
                Route::get('{merchant}', [MerchantController::class, 'show'])->name('show');
                Route::get('{merchant}/edit', [MerchantController::class, 'edit'])->name('edit');
                Route::patch('{merchant}', [MerchantController::class, 'update'])->name('update');

                // Status changes: GET opens the confirm slideover, POST does it.
                foreach (['approve', 'request-changes', 'reject', 'suspend', 'reactivate'] as $review) {
                    Route::get("{merchant}/{$review}", [MerchantReviewController::class, 'confirm'])
                        ->defaults('review', $review)
                        ->name("confirm-{$review}");
                }

                Route::controller(MerchantReviewController::class)->group(function () {
                    Route::post('{merchant}/approve', 'approve')->name('approve');
                    Route::post('{merchant}/request-changes', 'requestChanges')->name('request-changes');
                    Route::post('{merchant}/reject', 'reject')->name('reject');
                    Route::post('{merchant}/suspend', 'suspend')->name('suspend');
                    Route::post('{merchant}/reactivate', 'reactivate')->name('reactivate');
                });

                Route::patch('{merchant}/documents/{document}/verify', [MerchantDocumentController::class, 'verify'])
                    ->name('documents.verify');

                Route::post('{merchant}/notes', [MerchantNoteController::class, 'store'])->name('notes.store');
            });
    });
