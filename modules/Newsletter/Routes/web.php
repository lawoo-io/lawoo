<?php

use Illuminate\Support\Facades\Route;
use Modules\Newsletter\Http\Controllers\CampaignController;
use Modules\Newsletter\Http\Controllers\SubscriberController;


Route::prefix('lawoo')->middleware(['web', 'auth', 'active.user'])->name('lawoo.')->group(function () {

    Route::prefix('newsletter')->name('newsletter.')->group(function () {

        // Campaigns
        Route::prefix('campaign')->name('campaign.')->group(function () {
            Route::get('/', [CampaignController::class, 'records'])->name('records');
            Route::get('/create', [CampaignController::class, 'create'])->name('records.create');
            Route::get('/{id}', [CampaignController::class, 'view'])->name('records.view');
        });

        // Subscribers
        Route::prefix('subscriber')->name('subscriber.')->group(function () {
            Route::get('/', [SubscriberController::class, 'records'])->name('records');
            Route::get('/create', [SubscriberController::class, 'create'])->name('records.create');
            Route::get('/{id}', [SubscriberController::class, 'view'])->name('records.view');
        });
    });
});
