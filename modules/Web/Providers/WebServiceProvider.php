<?php

namespace Modules\Web\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Web\Extends\Traits\ExtendUser;
use Modules\Web\Extends\Models\ExtendUserModel;

class WebServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'web';

    public function register(): void
    {
        // same code
    }

    public function boot(): void
    {
        /**
         * Register RouteServiceProvider
         */
        $this->app->register(RouteServiceProvider::class);

        /**
         * Load Migrations
         */
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        /**
         * Load Translations
         */
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'web');

        /**
         * Load Helpers
         */
        require_once __DIR__ . '/../Helpers/helpers.php';

    }
}
