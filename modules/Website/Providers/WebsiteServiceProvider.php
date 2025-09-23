<?php

namespace Modules\Website\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class WebsiteServiceProvider extends ServiceProvider
{
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

        /**
         * Register RouteServiceProvider
         */
        $this->app->register(RouteServiceProvider::class);

        /**
         * Load Translations from same Module
         */
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang');

        /**
         * Load json translation
         */
        $this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang/strings');

    }
}
