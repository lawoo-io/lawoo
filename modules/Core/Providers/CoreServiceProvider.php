<?php

namespace Modules\Core\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Console\Commands\InitCommand;
use Modules\Core\Console\Commands\MakeLivewireCommand;
use Modules\Core\Console\Commands\MakeModelCommand;
use Modules\Core\Console\Commands\MakeModuleCommand;
use Modules\Core\Console\Commands\MakeViewCommand;
use Modules\Core\Console\Commands\ModulesResBuildCommand;
use Modules\Core\Console\Commands\ModulesCheckCommand;
use Modules\Core\Console\Commands\ModulesInstallCommand;
use Modules\Core\Console\Commands\ModulesRemoveCommand;
use Modules\Core\Console\Commands\ModulesUpdateCommand;
use Modules\Core\Console\Commands\SyncUiStrings;
use Modules\Core\Database\Seeders\ModuleCategorySeeders;
use Modules\Core\Helpers\RouteHelper;
use Modules\Core\Http\Middleware\SetLocale;
use Modules\Core\Models\Override;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Repositories\ModuleRepository;

class CoreServiceProvider extends ServiceProvider
{

    public function register(): void {

        $this->commands([
            InitCommand::class,
            ModulesCheckCommand::class,
            ModulesInstallCommand::class,
            ModulesUpdateCommand::class,
            ModulesRemoveCommand::class,
            ModulesResBuildCommand::class,
            ModulesUpdateCommand::class,
            MakeModelCommand::class,
            MakeLivewireCommand::class,
            MakeViewCommand::class,
            MakeModuleCommand::class,
            SyncUiStrings::class,
        ]);

        // Bind the RouteHelpers class to the service container as a singleton.
        // This ensures that only one instance of RouteHelpers is created throughout the application's lifecycle.
        $this->app->singleton('route.helper', function ($app) {
            return new RouteHelper();
        });

        $this->app->singleton(RouteServiceProvider::class);
    }

    public function boot(Kernel $kernel): void {

        /**
         * Load Migrations
         */
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        /**
         * Merge Config
         */
        $this->mergeConfigFrom(__DIR__ . '/../config.php', 'app');

        /**
         * Register Kernel
         */
        $kernel->prependMiddlewareToGroup('web', SetLocale::class);
        Blade::directive('_t', function ($expression) {
            // Dies ist der String, der in den Klammern steht, z.B. "'Willkommen auf Lawoo!', 'Core'"
            // Wir müssen sicherstellen, dass dieser Ausdruck korrekt an __t() übergeben wird.
            return "<?php echo __t({$expression}); ?>";
        });

        /**
         * Register RouteServiceProvider
         */
        $this->app->register(RouteServiceProvider::class);

        /**
         * Register Seeds
         */
        $this->registerSeeds();

        /**
         * Register activated modules
         */
        $moduleRepo = app(ModuleRepository::class);
        $moduleRepo->registerEnabledModules(); // ⬅ Nur ServiceProvider!

        $moduleRepo->registerLivewireForAll(); // ⬅ Separater Scan danach

        /**
         * Register override classes
         */
        $this->registerOverrides();

        /**
         * Load the global helper file of your module.
         * This ensures that the lroute() and lurl() functions are available throughout the application.
         */
        require_once __DIR__ . '/../Helpers/helpers.php';

        /**
         * Load Translations from same Module
         */
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'core');
    }

    protected function registerSeeds(): void {
        if (App::runningInConsole() && $this->app->runningUnitTests() === false) {
            $this->app->afterResolving(Seeder::class, function (Seeder $seeder) {
                $seeder->call(ModuleCategorySeeders::class);
            });
        }
    }

    /**
     * Register override classes
     * @return void
     */
    protected function registerOverrides(): void {
        if (!Schema::hasTable('overrides')) return;

        $overrides = Override::all();

        foreach ($overrides as $override) {
            if (class_exists($override->original_class) && class_exists($override->override_class)) {
                $this->app->bind($override->original_class, $override->override_class);
            }
        }

    }
}
