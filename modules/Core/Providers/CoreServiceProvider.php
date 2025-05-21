<?php

namespace Modules\Core\Providers;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
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
use Modules\Core\Database\Seeders\ModuleCategorySeeders;
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
        ]);
    }

    public function boot(): void {

        /**
         * Load Migrations
         */
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        /**
         * Merge Config
         */
        $this->mergeConfigFrom(__DIR__ . '/../config.php', 'core');

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
