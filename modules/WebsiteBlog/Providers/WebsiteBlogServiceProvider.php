<?php

namespace Modules\WebsiteBlog\Providers;

use Illuminate\Support\ServiceProvider;

class WebsiteBlogServiceProvider extends ServiceProvider
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

        // Load routes
         $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang');

        /**
         * Load json translation
         */
        $this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang');

    }
}
