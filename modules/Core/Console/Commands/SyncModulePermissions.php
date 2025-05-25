<?php

namespace Modules\Core\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Contracts\PermissionRegistrarInterface;

class SyncModulePermissions extends Command
{
    protected $signature = 'lawoo:rbac:sync {module?} {--validate}';
    protected $description = 'Sync PHP-based permissions and roles';

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
        $configFile = base_path("modules/{$module}/Config/RolesAndPermissions.php");

        if (!file_exists($configFile)) {
            $this->error("No RolesAndPermissions.php found for module: {$module}");
            return;
        }

        if ($this->option('validate')) {
            $this->validateConfigFile($configFile, $module);
            return;
        }

        try {
            $config = require $configFile;

            if (!empty($config)) {
                foreach ($config as $roleData) {
                    $this->processConfigRole($registrar, $roleData, $module);
                }
            }

            $this->info("PHP config permissions synced for module: {$module}");

        } catch (\Exception $e) {
            $this->error("Error syncing config for module {$module}: " . $e->getMessage());
        }
    }

    protected function validateConfigFile(string $configFile, string $module): void
    {
        try {
            $config = require $configFile;

            $this->info("✅ PHP config valid for module: {$module}");

            if (!empty($config)) {
                $this->info("Found " . count($config) . " roles");

                foreach ($config as $role) {
                    $permissionCount = isset($role['permissions']) ? count($role['permissions']) : 0;
                    $this->line("  - {$role['name']} ({$permissionCount} permissions)");
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Config validation failed: " . $e->getMessage());
        }
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
    }

    private function processConfigRole(PermissionRegistrarInterface $registrar, array $roleData, string $module): void
    {
        // Permission registrieren
        $permissionSlugs = [];

        if (isset($roleData['permissions'])) {
            foreach ($roleData['permissions'] as $slug => $permissionData) {
                $permissionRecord = [
                    'slug' => $slug,
                    'name' => $permissionData['name'] ?? $this->generatePermissionName($slug),
                    'description' => $permissionData['description'] ?? "Permission: {$slug}",
                    'module' => strtolower($module),
                    'resource' => $permissionData['resource'] ?? $this->extractResource($slug),
                    'action' => $permissionData['action'] ?? $this->extractAction($slug),
                    'is_system' => $permissionData['is_system'] ?? false
                ];

                $registrar->registerPermissions(strtolower($module), [$permissionRecord]);
                $permissionSlugs[] = $slug;
            }
        }

        // Rolle erstellen
        $roleRecord = [
            'slug' => $roleData['slug'],
            'name' => $roleData['name'],
            'description' => $roleData['description'] ?? '',
            'module' => strtolower($module),
            'is_system' => $roleData['is_system'] ?? false,
            'permissions' => $permissionSlugs
        ];

        $registrar->createRoleWithPermissions(strtolower($module), $roleRecord);
    }

    private function generatePermissionName(string $slug): string
    {
        $parts = explode('.', $slug);
        if (count($parts) >= 3) {
            $action = ucfirst($parts[2]);
            $resource = ucfirst($parts[1]);
            return "{$action} {$resource}";
        }

        return ucwords(str_replace(['.', '_', '-'], ' ', $slug));
    }

    private function extractResource(string $slug): string
    {
        $parts = explode('.', $slug);
        return $parts[1] ?? 'general';
    }

    private function extractAction(string $slug): string
    {
        $parts = explode('.', $slug);
        return $parts[2] ?? 'access';
    }
}
