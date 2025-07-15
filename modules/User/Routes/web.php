<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

Route::prefix('lawoo')->middleware(['web', 'auth', 'active.user'])->name('lawoo.')->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'records'])->name('records');
        Route::get('/create', [UserController::class, 'create'])->name('records.create');
        Route::get('/{id}', [UserController::class, 'view'])->name('records.view');

        // File
        Route::get('/file/{id}', [UserController::class, 'viewFile'])->name('view.file');
    });
});
