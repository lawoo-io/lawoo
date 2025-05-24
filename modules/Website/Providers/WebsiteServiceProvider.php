<?php

namespace Modules\Website\Providers;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Modules\Website\Http\Middleware\WebsiteLocaleFromUrl;

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
    public function boot(Kernel $kernel): void
    {
        /**
         * Register Kernel
         */
        $kernel->prependMiddlewareToGroup('web', WebsiteLocaleFromUrl::class);

        /**
         * Register RouteServiceProvider
         */
        $this->app->register(RouteServiceProvider::class);

        /**
         * Load Translations from same Module
         */
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'website');

    }
}
