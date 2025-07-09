<?php

use Illuminate\Support\Facades\Route;
use Modules\Web\Http\Controllers\CompaniesController;
use Modules\Web\Http\Controllers\CountryController;
use Modules\Web\Http\Controllers\LanguagesController;
use Modules\Web\Http\Controllers\ModulesController;
use Modules\Web\Http\Controllers\ProfileController;
use Modules\Web\Http\Controllers\RolesController;
use Modules\Web\Http\Controllers\SettingsController;
use Modules\Web\Http\Controllers\TranslationsController;
use Modules\Web\Http\Controllers\VerifyEmailController;

Route::prefix('lawoo')->middleware(['web', 'auth', 'active.user'])->name('lawoo.')->group(function () {

    Route::get('/', function(){
        return view('web.index');
    })->name('dashboard');

    // Profile Routes - Hybrid: Controller Views + Livewire Components
    Route::middleware(['web', 'auth'])->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'profile'])->name('view');
        Route::redirect('/', 'profile/form');
        Route::get('/form', [ProfileController::class, 'profile'])->name('form');
        Route::get('/password', [ProfileController::class, 'password'])->name('password');
        Route::get('/appearance', [ProfileController::class, 'appearance'])->name('appearance');
    });

    // Modules
    Route::get('/modules', [ModulesController::class, 'records'])->name('modules');
    Route::get('/modules/check', [ModulesController::class, 'check'])->name('modules.check');

    // Settings
    Route::middleware(['web', 'auth'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');

        // Roles
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RolesController::class, 'records'])->name('records');
            Route::get('/{id}', [RolesController::class, 'view'])->name('records.view');
        });

        // Companies
        Route::prefix('companies')->name('companies.')->group(function () {
            Route::get('/', [CompaniesController::class, 'records'])->name('records');
            Route::get('/create', [CompaniesController::class, 'create'])->name('records.create');
            Route::get('/{id}', [CompaniesController::class, 'view'])->name('records.view');
        });

        // Countries
        Route::get('/countries', [CountryController::class, 'records'])->name('countries');
        Route::get('/countries/create', [CountryController::class, 'create'])->name('countries.create');
        Route::get('/countries/{id}', [CountryController::class, 'view'])->name('countries.view');

        // Languages
        Route::get('/languages', [LanguagesController::class, 'records'])->name('languages');

        // Translations
        Route::get('/translations', [TranslationsController::class, 'records'])->name('translations');
        Route::get('/translations/{id}', [TranslationsController::class, 'view'])->name('translations.view');

    });

});

Route::get('/', function () {
    return redirect()->route('dashboard');
});

/**
 * Auth Guests
 */
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('modules.web.auth.login');
    })->name('login'); // <== DAS FEHLTE
});

/**
 * Auth Routes
 */
Route::middleware(['auth'])->group(function () {
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::post('logout', \Modules\Web\Http\Livewire\Auth\Logout::class)
        ->name('logout');
