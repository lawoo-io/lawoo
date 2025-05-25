<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Contracts\PermissionRegistrarInterface;

class SyncModulePermissions extends Command
{
    protected $signature = 'lawoo:rbac:sync {module?} {--force}';
    protected $description = 'Sync module permissions with database';

    public function handle(PermissionRegistrarInterface $registrar): int
    {
        $module = $this->argument('module');

        if ($module) {
            $this->syncSingleModule($module, $registrar);
        } else {
            $this->syncAllModules($registrar);
        }

        return Command::SUCCESS;
    }

    protected function syncSingleModule(string $module, PermissionRegistrarInterface $registrar): void
    {
        $permissionsFile = base_path("modules/{$module}/Config/permissions.php");

        if (!file_exists($permissionsFile)) {
            $this->error("No permissions.php found for module: {$module}");
            return;
        }

        $permissions = require $permissionsFile;
        $registrar->syncModulePermissions($module, $permissions);

        $this->info("Permissions synced for module: {$module}");
    }

    protected function syncAllModules(PermissionRegistrarInterface $registrar): void
    {
        $modulesPath = base_path('modules');
        $modules = array_filter(scandir($modulesPath), function ($item) use ($modulesPath) {
            return $item !== '.' && $item !== '..' && is_dir($modulesPath . '/' . $item);
        });

        foreach ($modules as $module) {
            $this->syncSingleModule($module, $registrar);
        }

        $this->info('All module permissions synced!');
    }
}
