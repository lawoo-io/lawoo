<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Modules\Core\Models\Module;
use Modules\Core\Services\Modules\ModuleRemover;
use Modules\Core\Services\RBACCleanupService;
use Modules\Core\Services\Schemas\MigrationManager;

class ModulesRemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lawoo:remove {name : The name of the module} {--remove-db}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes modules that have been installed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $module = $this->argument('name');

        $modules = Module::getRequiredByDependents($module);
        $names = array_map(fn($m) => $m->system_name, $modules);

        foreach ($names as $name) {
            self::removeModule($name);
        }

        self::removeModule($module, true);
    }

    public function removeModule(string $name, bool $self = false): void
    {
        $result = ModuleRemover::run($name);

        if($this->option('remove-db')){
            MigrationManager::removeDb($name);
        }

        // RBAC Cleanup hinzufügen
        $rbacCleanup = app(RBACCleanupService::class);
        $results['rbac_cleanup'] = $rbacCleanup->removeModuleRBAC($name);

        $this->components->{$result['type']}($result['message']);
    }
}
