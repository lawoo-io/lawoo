<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;

class UserServiceBinding extends ServiceProvider
{
    /**
     * Register the User model binding
     */
    public function register(): void
    {
        // Bind the original User model to our Extended User
        $this->app->bind(
            \App\Models\User::class,
            \Modules\Core\Models\ExtendedUser::class
        );
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        // Ensure the binding works with dependency injection
        $this->app->resolving(\App\Models\User::class, function ($user, $app) {
            // Additional setup if needed
        });
    }
}
