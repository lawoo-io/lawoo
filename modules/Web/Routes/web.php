<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('lawoo');
});


/**
 * Auth Guests
 */
Route::middleware('guest')->group(function () {
    Route::get('login', function () {
        return view('web.auth.login');
    })->name('login'); // <== DAS FEHLTE
});

/**
 * Auth Auth
 */
Route::middleware('auth')->group(function () {
    Route::get('lawoo', function () {
        return view('web.index');
    })->name('lawoo');
});

Route::post('logout', \Modules\Web\Http\Livewire\Auth\Logout::class)
        ->name('logout');
