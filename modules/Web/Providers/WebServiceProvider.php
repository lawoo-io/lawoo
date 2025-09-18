<?php

namespace Modules\Web\Providers;

use Illuminate\Database\Seeder;
use Illuminate\Support\ServiceProvider;
use Modules\Web\Database\Seeders\CommunicationTypeSeeders;
use Modules\Web\Database\Seeders\CompanySeeders;
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
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang');

        /**
         * Load json translation
         */
        $this->loadJsonTranslationsFrom(__DIR__.'/../Resources/lang/strings');

        /**
         * Load Helpers
         */
        require_once __DIR__ . '/../Helpers/helpers.php';

    }

    protected function registerSeeders(): void
    {
        if ($this->app->runningInConsole() && $this->app->runningUnitTests() === false) {
            $this->app->afterResolving(Seeder::class, function (Seeder $seeder) {
                // Companies
                $seeder->call(CompanySeeders::class);
            });
        }
    }
}
