<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Models\Module;
use Modules\Core\Services\Modules\ModuleInstaller;
use Modules\Core\Services\Resources\OverrideViews;
use Modules\Core\Services\Resources\ResourceBuild;
use Modules\Core\Services\Schemas\DbFieldManager;
use Modules\Core\Services\Schemas\MigrationManager;
use Modules\Core\Services\Schemas\SchemaChecker;
use Modules\Core\Services\SettingSynchronizerService;
use Modules\Core\Services\TranslationImporter;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ModulesInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lawoo:install {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the modules';

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

            $modules = Module::getDependenciesForInstall($module);
            $names = array_map(fn($m) => $m->system_name, $modules);

            foreach ($names as $name) {
                self::installModule($name);
            }

            self::installModule($module);


        } catch (\RuntimeException $e) {
            $this->error("❌ " . $e->getMessage());
            return CommandAlias::FAILURE;
        }

    }

    public function installModule(string $module): void
    {

        /**
         * Run module installer and display results
         */
        $result = ModuleInstaller::run($module);
        $this->components->{$result['type']}($result['message']);
        if ($result['type'] === 'warning' || $result['type'] === 'error') return;


        /**
         * Run Resources builder and display results
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
         * Run OverrideViews
         */
        $result = OverrideViews::run([$module]);
        $this->components->{$result['type']}($result['message']);

        /**
         * Run translation command
         */
        Artisan::call('lawoo:sync-ui-strings ' . $module . ' --cleanup');

        /*
         * Register permissions
         */
        Artisan::call('lawoo:rbac:sync ' . $module);

        /*
         * Sync navigation
         */
        Artisan::call('lawoo:nav:sync ' . $module);

        /**
         * Import or Update Default Translations
         */
        $importer = app(TranslationImporter::class);
        $importer->importModuleTranslations($module);

        /**
         * Sync settings
         */
        SettingSynchronizerService::run($module);
    }
}
