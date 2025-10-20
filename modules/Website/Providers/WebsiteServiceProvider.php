<?php

namespace Modules\Website\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Website\Models\Website;

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

        /**
         * Register AnonymousComponentNamespace
         */
        self::registerAnonymousComponentNamespace();
    }

    public static function registerAnonymousComponentNamespace(): void
    {
        $websites = Website::where('is_active', true)->get();
        foreach ($websites as $website) {
            $directory = 'websites/website_'.$website->slug . '/components';
            $prefix = $website->slug;
            Blade::anonymousComponentNamespace($directory, $prefix);
        }
    }

}
