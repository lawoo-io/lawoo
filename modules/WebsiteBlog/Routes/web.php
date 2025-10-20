<?php

use Illuminate\Support\Facades\Route;
use Modules\WebsiteBlog\Http\Controllers\BlogCategoryController;
use Modules\WebsiteBlog\Http\Controllers\BlogPostController;

Route::prefix('lawoo')->middleware(['web', 'auth', 'active.user'])->name('lawoo.')->group(function () {
    Route::prefix('website')->name('website.')->group(function () {

        // Blog
        Route::prefix('blog')->name('blog.')->group(function () {

            // Posts
            Route::get('/posts', [BlogPostController::class, 'list'])->name('posts');
            Route::get('/posts/create', [BlogPostController::class, 'create'])->name('posts.create');
            Route::get('/posts/{id}', [BlogPostController::class, 'update'])->name('posts.update');

            // Categories
            Route::get('/categories', [BlogCategoryController::class, 'list'])->name('categories');
            Route::get('/categories/create', [BlogCategoryController::class, 'create'])->name('categories.create');
            Route::get('/categories/{id}', [BlogCategoryController::class, 'update'])->name('categories.update');
        });
    });
});
