<?php

use Illuminate\Support\Facades\Route;
use Modules\Web\Http\Controllers\ProfileController;
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
