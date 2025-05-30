<?php

namespace Modules\Core\Services;

use Modules\Core\Models\Navigation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class NavigationService
{
    /**
     * Synchronize navigation config files to database for specific module
     */
    public function syncModule(string $moduleName): array
    {
        $configData = $this->loadModuleConfig($moduleName);

        if (empty($configData)) {
            return [
                'module' => $moduleName,
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'message' => 'No navigation config found'
            ];
        }

        return $this->processNavigationItems($moduleName, $configData);
    }

    /**
     * Synchronize all modules
     */
    public function syncAllModules(): array
    {
        $modules = $this->getAvailableModules();
        $results = [];

        foreach ($modules as $module) {
            $results[$module] = $this->syncModule($module);
        }

        return $results;
    }

    /**
     * Load navigation config for a specific module
     */
    protected function loadModuleConfig(string $moduleName): array
    {
        $configPath = base_path("modules/{$moduleName}/Config/Navigation.php");

        if (!File::exists($configPath)) {
            return [];
        }

        try {
            return require $configPath;
        } catch (\Exception $e) {
            Log::error("Failed to load navigation config for {$moduleName}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process navigation items from config
     */
    protected function processNavigationItems(string $moduleName, array $configData): array
    {
        $stats = [
            'module' => $moduleName,
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        // First pass: Create/Update items without parent relationships
        foreach ($configData as $route => $config) {
            $stats['processed']++;
            $result = $this->syncNavigationItem($route, $config, $moduleName);
            $stats[$result]++;
        }

        // Second pass: Update parent relationships
        $this->updateParentRelationships($moduleName, $configData);

        return $stats;
    }

    /**
     * Sync individual navigation item
     */
    protected function syncNavigationItem(string $route, array $config, string $moduleName): string
    {
        $existing = Navigation::where('route', $route)
            ->where('module', $moduleName)
            ->first();

        if ($existing) {
            return $this->updateExistingItem($existing, $config);
        } else {
            return $this->createNewItem($route, $config, $moduleName);
        }
    }

    /**
     * Update existing navigation item (respects user modifications)
     */
    protected function updateExistingItem(Navigation $navigation, array $config): string
    {
        $updates = [];

        // Only update if not user-modified
        if (!$navigation->is_user_modified) {
            if (isset($config['name']) && $navigation->name !== $config['name']) {
                $updates['name'] = $config['name'];
            }

            if (isset($config['sort_order']) && $navigation->sort_order !== $config['sort_order']) {
                $updates['sort_order'] = $config['sort_order'];
            }
        }

        // Always update these fields (not user-modifiable)
        $alwaysUpdate = ['middleware', 'icon', 'group_name', 'group_order', 'level'];
        foreach ($alwaysUpdate as $field) {
            if (isset($config[$field]) && $navigation->{$field} !== $config[$field]) {
                $updates[$field] = $config[$field];
            }
        }

        if (!empty($updates)) {
            $navigation->update($updates);
            return 'updated';
        }

        return 'skipped';
    }

    /**
     * Create new navigation item
     */
    protected function createNewItem(string $route, array $config, string $moduleName): string
    {
        Navigation::create([
            'route' => $route,
            'module' => $moduleName,
            'name' => $config['name'],
            'level' => $config['level'] ?? 0,
            'sort_order' => $config['sort_order'] ?? 100,
            'middleware' => $config['middleware'] ?? null,
            'icon' => $config['icon'] ?? null,
            'group_name' => $config['group_name'] ?? null,
            'group_order' => $config['group_order'] ?? 100,
            'is_active' => $config['is_active'] ?? true,
            'is_user_modified' => false,
        ]);

        return 'created';
    }

    /**
     * Update parent relationships after all items are created/updated
     */
    protected function updateParentRelationships(string $moduleName, array $configData): void
    {
        foreach ($configData as $route => $config) {
            if (isset($config['parent'])) {
                $child = Navigation::where('route', $route)
                    ->where('module', $moduleName)
                    ->first();

                $parent = Navigation::where('route', $config['parent'])->first();

                if ($child && $parent) {
                    $child->update(['parent_id' => $parent->id]);
                }
            }
        }
    }

    /**
     * Get all available modules with Navigation configs
     */
    public function getAvailableModules(): array
    {
        $modulesPath = base_path('modules');
        $modules = [];

        if (!File::exists($modulesPath)) {
            return $modules;
        }

        $directories = File::directories($modulesPath);

        foreach ($directories as $directory) {
            $moduleName = basename($directory);
            $configPath = "{$directory}/Config/Navigation.php";

            if (File::exists($configPath)) {
                $modules[] = $moduleName;
            }
        }

        return $modules;
    }

    /**
     * Remove navigation items for a module
     */
    public function removeModule(string $moduleName): array
    {
        $count = Navigation::where('module', $moduleName)->count();
        Navigation::where('module', $moduleName)->delete();

        return [
            'module' => $moduleName,
            'removed_items' => $count
        ];
    }

    /**
     * Validate navigation config
     */
    public function validateConfig(string $moduleName): array
    {
        $config = $this->loadModuleConfig($moduleName);
        $errors = [];

        foreach ($config as $route => $item) {
            // Check required fields
            if (empty($item['name'])) {
                $errors[] = "Missing 'name' for route: {$route}";
            }

            // Check level validity
            if (isset($item['level']) && !in_array($item['level'], [0, 1, 2])) {
                $errors[] = "Invalid level for route: {$route}. Must be 0, 1, or 2";
            }

            // Check icon only for level 0 and 1
            if (isset($item['icon']) && isset($item['level']) && !in_array($item['level'], [0, 1])) {
                $errors[] = "Icon not allowed for level 2 route: {$route}";
            }

            // Check parent exists
            if (isset($item['parent']) && !isset($config[$item['parent']])) {
                $errors[] = "Parent route '{$item['parent']}' not found for: {$route}";
            }
        }

        return $errors;
    }
}
