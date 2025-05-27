<?php

namespace Modules\Core\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Console\Commands\ClearPermissionCache;
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
use Modules\Core\Console\Commands\RemoveModuleRBAC;
use Modules\Core\Console\Commands\SyncModulePermissions;
use Modules\Core\Console\Commands\SyncUiStrings;
use Modules\Core\Database\Seeders\ModuleCategorySeeders;
use Modules\Core\Database\Seeders\RbacSeeder;
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
            ClearPermissionCache::class,
            SyncModulePermissions::class,
            RemoveModuleRBAC::class,
        ]);

        // Bind the RouteHelpers class to the service container as a singleton.
        $this->app->singleton('route.helper', function ($app) {
            return new RouteHelper();
        });

        $this->app->singleton(RouteServiceProvider::class);

        // RBAC Service Provider registrieren - NUR EINMAL
        $this->app->register(RbacServiceProvider::class);
    }

    public function boot(): void
    {
        /**
         * Load Kernel
         */
        $kernel = $this->app->make(Kernel::class);

        /**
         * Register Locations
         */
        $kernel->appendMiddlewareToGroup('web', SetLocale::class);

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
        Blade::directive('_t', function ($expression) {
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
        $moduleRepo->registerEnabledModules();
        $moduleRepo->registerLivewireForAll();

        /**
         * Register override classes
         */
        $this->registerOverrides();

        /**
         * Load the global helper file
         */
        require_once __DIR__ . '/../Helpers/helpers.php';

        /**
         * Load Translations
         */
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'core');

        /**
         * Register RBAC Middleware
         */
        $this->registerRbacMiddleware();
    }

    protected function registerRbacMiddleware(): void
    {
        $router = $this->app->make(\Illuminate\Routing\Router::class);

        // Register RBAC middleware aliases
        $router->aliasMiddleware('role', \Modules\Core\Http\Middleware\HasRole::class);
        $router->aliasMiddleware('permission', \Modules\Core\Http\Middleware\HasPermission::class);
        $router->aliasMiddleware('role.or.permission', \Modules\Core\Http\Middleware\HasRoleOrPermission::class);
        $router->aliasMiddleware('active.user', \Modules\Core\Http\Middleware\CheckActiveUser::class);

        // Add to middleware groups - AKTIVIERT nach Tests
         $router->pushMiddlewareToGroup('web', \Modules\Core\Http\Middleware\CheckActiveUser::class);
    }

    protected function registerSeeds(): void {
        if (App::runningInConsole() && $this->app->runningUnitTests() === false) {
            $this->app->afterResolving(Seeder::class, function (Seeder $seeder) {
                // ModuleCategories
                $seeder->call(ModuleCategorySeeders::class);

                // PermissionSeeders
//                $seeder->call(RbacSeeder::class);
            });
        }
    }

    /**
     * Register override classes
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
