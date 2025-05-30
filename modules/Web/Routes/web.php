<?php

use Illuminate\Support\Facades\Route;
use Modules\Web\Http\Controllers\UserController;
use Modules\Web\Http\Controllers\VerifyEmailController;

Route::prefix('lawoo')->middleware(['web', 'auth', 'active.user'])->group(function () {

    Route::get('/', function(){
        return view('web.index');
    })->name('dashboard');

    // Profile Routes - Hybrid: Controller Views + Livewire Components
    Route::middleware(['web', 'auth'])->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [UserController::class, 'profile'])->name('view');
        Route::redirect('/', 'profile/form');
        Route::get('/form', [UserController::class, 'profile'])->name('form');


        Route::get('/password', [UserController::class, 'password'])->name('password');
    });

    // User Management Routes (für später - Admin-Bereich)
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

});

Route::get('/', function () {
    return redirect()->route('dashboard');
});

//Route::middleware(['auth', 'active.user'])->group(function () {
//    Route::get('lawoo', function () {
////        App::setLocale(session('locale'));
//        return view('web.index');
//    })->name('lawoo');
//});


/**
 * Auth Guests
 */
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('web.auth.login');
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
