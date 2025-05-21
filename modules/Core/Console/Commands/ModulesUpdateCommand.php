<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Services\Modules\ModuleUpdater;
use Modules\Core\Services\Resources\OverrideViews;
use Modules\Core\Services\Resources\ResourceBuild;
use Modules\Core\Services\Schemas\DbFieldManager;
use Modules\Core\Services\Schemas\MigrationManager;
use Modules\Core\Services\Schemas\SchemaChecker;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ModulesUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lawoo:update {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            /**
             * Load module Name
             */
            $module = $this->argument('module');

            /**
             * Run module updater and display results
             */
            $result = ModuleUpdater::run($module);
            $this->components->{$result['type']}($result['message']);

            /**
             * Run resources builder and display results
             */
            $result = ResourceBuild::run([$module]);
            $this->components->{$result['type']}($result['message']);

            /**
             * Run Schema Generator and display results
             */
            $result = SchemaChecker::run($module);
            $this->components->{$result['type']}($result['message']);

            /**
             * Run DbFieldManager
             */
            DbFieldManager::run($module);

            /**
             * Run MigrationManager
             */
            MigrationManager::run($module);

            /**
             * Run Artisan migrate command
             */
//            Artisan::call('migrate --path=modules/' . $module . '/Database/Migrations');

            /**
             * Run OverrideViews
             */
//            $result = OverrideViews::run([$module]);

        } catch (\RuntimeException $e) {
            $this->error("❌ " . $e->getMessage());
            return CommandAlias::FAILURE;
        }

    }
}
