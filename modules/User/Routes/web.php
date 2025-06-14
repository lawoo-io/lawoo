<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::prefix('lawoo')->middleware(['web', 'auth', 'active.user'])->name('lawoo.')->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'lists'])->name('lists');
        Route::get('/{id}', [UserController::class, 'view'])->name('lists.view');
        Route::get('/create', [UserController::class, 'create'])->name('create');
//        Route::get('/{user}', [UserController::class, 'show'])->name('show');
//        Route::post('/', [UserController::class, 'store'])->name('store');
//        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
//        Route::put('/{user}', [UserController::class, 'update'])->name('update');
//        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});
