<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\AssetController;
use Modules\Website\Http\Controllers\LayoutController;
use Modules\Website\Http\Controllers\PageController;
use Modules\Website\Http\Controllers\ThemeController;
use Modules\Website\Http\Controllers\WebsiteController;

Route::prefix('lawoo')->middleware(['web', 'auth', 'active.user'])->name('lawoo.')->group(function () {
    Route::prefix('website')->name('website.')->group(function () {

        // Websites
        Route::get('/', [WebsiteController::class, 'records'])->name('records');
        Route::get('/create', [WebsiteController::class, 'create'])->name('records.create');
        Route::get('/website/{id}', [WebsiteController::class, 'view'])->name('records.view');

        // Pages
        Route::prefix('pages')->name('pages.')->group(function (){
            Route::get('/', [PageController::class, 'records'])->name('records');
            Route::get('/create', [PageController::class, 'create'])->name('records.create');
            Route::get('/{id}', [PageController::class, 'view'])->name('records.view');
        });

        // Themes
        Route::prefix('themes')->name('themes.')->group(function () {
            Route::get('/', [ThemeController::class, 'records'])->name('records');
            Route::get('/create', [ThemeController::class, 'create'])->name('records.create');
            Route::get('/{id}', [ThemeController::class, 'view'])->name('records.view');
        });

        // Layouts
        Route::prefix('layouts')->name('layouts.')->group(function () {
            Route::get('/', [LayoutController::class, 'records'])->name('records');
            Route::get('/create', [LayoutController::class, 'create'])->name('records.create');
            Route::get('/{id}', [LayoutController::class, 'view'])->name('records.view');
        });

        // Assets
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [AssetController::class, 'records'])->name('records');
            Route::get('/create', [AssetController::class, 'create'])->name('records.create');
            Route::get('/{id}', [AssetController::class, 'view'])->name('records.view');
        });
    });
});
