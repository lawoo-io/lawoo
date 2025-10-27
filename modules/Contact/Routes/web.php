<?php

use Modules\Contact\Http\Controllers\ContactController;

Route::prefix('lawoo')->middleware(['web', 'auth', 'active.user'])->name('lawoo.')->group(function () {
    Route::prefix('contact')->name('contact.')->group(function () {

        // Contacts
        Route::get('/contacts', [ContactController::class, 'list'])->name('list');
        Route::get('/contacts/create', [ContactController::class, 'create'])->name('list.create');
        Route::post('/contacts/{id}', [ContactController::class, 'update'])->name('list.update');
    });
});
