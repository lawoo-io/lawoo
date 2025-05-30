<?php

namespace Modules\Core\Abstracts;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Traits\RegistersRBAC;

abstract class ModuleServiceProvider extends ServiceProvider
{
    use RegistersRBAC;

    /**
     * Single module name (for backward compatibility)
     */
    protected string $moduleName = '';

    /**
     * Multiple module names (for extended modules)
     */
    protected array $moduleNames = [];

    /**
     * Boot method - automatically registers RBAC if config exists
     */
    public function boot(): void
    {
        // Auto-register RBAC from config
        $this->autoRegisterRBAC();

        // Call child boot method
        $this->bootModule();

        /**
         * Load Translations
         */
//        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'core');
    }

    /**
     * Child classes can override this instead of boot()
     */
    protected function bootModule(): void
    {
        // Override in child classes
    }

    /**
     * Automatically register RBAC from PHP config
     */
    protected function autoRegisterRBAC(): void
    {
        $modules = $this->getModulesToRegister();

        if (empty($modules)) {
            return;
        }

        foreach ($modules as $module) {
            $this->registerRBACForModule($module);
        }
    }

    /**
     * Get modules to register (either single or multiple)
     */
    protected function getModulesToRegister(): array
    {
        // Priorität: moduleNames Array, dann fallback auf einzelnen moduleName
        if (!empty($this->moduleNames)) {
            return $this->moduleNames;
        }

        if (!empty($this->moduleName)) {
            return [$this->moduleName];
        }

        return [];
    }

    /**
     * Register RBAC for a single module
     */
    protected function registerRBACForModule(string $module): void
    {
        $modulePath = $this->getModulePath();

        // 1. Modul-spezifische Config probieren
        $moduleConfigPath = $modulePath . "/Config/{$module}/RolesAndPermissions.php";
        if (file_exists($moduleConfigPath)) {
            $this->registerRBACFromConfig($moduleConfigPath, $module);
            return;
        }

        // 2. Haupt-Config mit Modul-Präfix probieren
        $prefixedConfigPath = $modulePath . "/Config/RolesAndPermissions_{$module}.php";
        if (file_exists($prefixedConfigPath)) {
            $this->registerRBACFromConfig($prefixedConfigPath, $module);
            return;
        }

        // 3. Fallback auf Standard-Config (für erstes Modul)
        if ($module === ($this->moduleNames[0] ?? $this->moduleName)) {
            $standardConfigPath = $modulePath . '/Config/RolesAndPermissions.php';
            if (file_exists($standardConfigPath)) {
                $this->registerRBACFromConfig($standardConfigPath, $module);
                return;
            }
        }

        // 4. Legacy permissions.php für einzelne Module
        $legacyConfigPath = $modulePath . "/Config/permissions_{$module}.php";
        if (file_exists($legacyConfigPath)) {
            $this->registerLegacyPermissions($legacyConfigPath, $module);
            return;
        }

        // 5. Standard legacy für erstes Modul
        if ($module === ($this->moduleNames[0] ?? $this->moduleName)) {
            $standardLegacyPath = $modulePath . '/Config/permissions.php';
            if (file_exists($standardLegacyPath)) {
                $this->registerLegacyPermissions($standardLegacyPath, $module);
            }
        }
    }

    /**
     * Register legacy permissions (only permissions, no roles)
     */
    protected function registerLegacyPermissions(string $configPath, string $module): void
    {
        try {
            $permissions = require $configPath;
            if (!empty($permissions)) {
                $registrar = app(\Modules\Core\Contracts\PermissionRegistrarInterface::class);
                $registrar->registerPermissions(strtolower($module), $permissions);
            }
        } catch (\Exception $e) {
            \Log::error("Error parsing legacy permissions for module {$module}: " . $e->getMessage());
        }
    }

    /**
     * Get module base path
     */
    protected function getModulePath(): string
    {
        $reflection = new \ReflectionClass($this);
        return dirname($reflection->getFileName(), 2); // Go up 2 levels from Providers/
    }

    /**
     * Helper method for manual RBAC registration
     */
    protected function registerModuleRBAC(array $rolesConfig): void
    {
        $this->registerRBACFromArray($rolesConfig, $this->moduleName);
    }
}
