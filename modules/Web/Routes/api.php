<?php

use Illuminate\Support\Facades\Route;
use \Modules\Web\Http\Api\ProfileController;

Route::middleware(['api', 'auth:sanctum'])->prefix('profile')->group(function () {

    Route::get('/', [ProfileController::class, 'view']);

    Route::put('/', [ProfileController::class, 'update']);

    Route::put('/change-password', [ProfileController::class, 'changePassword']);

});
