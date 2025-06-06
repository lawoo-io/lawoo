<?php

namespace Modules\User\Providers;

use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{

//    protected string $moduleName = 'user';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        * Register RouteServiceProvider
        */
        $this->app->register(RouteServiceProvider::class);

        /*
         * Load translations
         */
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'user');

    }
}
