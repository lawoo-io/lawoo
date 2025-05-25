<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;
use Modules\Core\Contracts\PermissionRegistrarInterface;

class PermissionRegistrar implements PermissionRegistrarInterface
{
    protected array $registeredPermissions = [];

    /**
     * Register permissions for a module
     */
    public function registerPermissions(string $module, array $permissions): void
    {
        foreach ($permissions as $permission) {
            $this->registerSinglePermission($module, $permission);
        }

        // Clear cache after registration
        $this->clearCache();
    }

    /**
     * Register a single permission
     */
    protected function registerSinglePermission(string $module, array $permission): void
    {
        $permissionData = array_merge([
            'module' => strtolower($module),
            'is_system' => false,
        ], $permission);

        // Validate required fields
        if (!isset($permissionData['slug']) || !isset($permissionData['name'])) {
            throw new \InvalidArgumentException("Permission must have 'slug' and 'name' fields");
        }

        // Create or update permission
        Permission::updateOrCreate(
            ['slug' => $permissionData['slug']],
            $permissionData
        );

        $this->registeredPermissions[$module][] = $permissionData['slug'];
    }

    /**
     * Get all permissions for a module
     */
    public function getModulePermissions(string $module): array
    {
        return Permission::where('module', strtolower($module))->pluck('slug')->toArray();
    }

    /**
     * Create role with permissions
     */
    public function createRoleWithPermissions(string $module, array $roleData): Role
    {
        $role = Role::updateOrCreate(
            ['slug' => $roleData['slug']],
            array_merge($roleData, ['module' => strtolower($module)])
        );

        if (isset($roleData['permissions'])) {
            $permissions = Permission::whereIn('slug', $roleData['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        $this->clearCache();

        return $role;
    }

    /**
     * Clear permission cache
     */
    public function clearCache(): void
    {
        // Alle bekannten RBAC Cache-Keys löschen
        $cacheKeys = [
            'rbac.permissions',
            'rbac.roles',
            'rbac.user_permissions',
            'rbac.role_permissions',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get cached permissions
     */
    public function getCachedPermissions(): array
    {
        return Cache::remember('rbac.permissions', 3600, function () {
            return Permission::all()->keyBy('slug')->toArray();
        });
    }

    /**
     * Sync module permissions (remove old, add new)
     */
    public function syncModulePermissions(string $module, array $permissions): void
    {
        // Get existing permissions for module
        $existingPermissions = $this->getModulePermissions($module);
        $newPermissionSlugs = collect($permissions)->pluck('slug')->toArray();

        // Remove permissions that no longer exist
        $permissionsToRemove = array_diff($existingPermissions, $newPermissionSlugs);
        if (!empty($permissionsToRemove)) {
            Permission::whereIn('slug', $permissionsToRemove)
                ->where('module', $module)
                ->where('is_system', false) // Don't remove system permissions
                ->delete();
        }

        // Add/update new permissions
        $this->registerPermissions($module, $permissions);
    }

    /**
     * Cleanup RBAC data for module removal
     */
    public function cleanupModuleRBAC(string $module, bool $forceSystem = false): array
    {
        $cleanupService = app(RBACCleanupService::class);
        return $cleanupService->removeModuleRBAC($module, ['force_system' => $forceSystem]);
    }

    /**
     * Check what RBAC data exists for a module
     */
    public function getModuleRBACInfo(string $module): array
    {
        $module = strtolower($module);

        $permissions = Permission::where('module', $module)->get();
        $roles = Role::where('module', $module)->get();

        return [
            'module' => $module,
            'permissions' => $permissions->count(),
            'permission_list' => $permissions->pluck('slug')->toArray(),
            'roles' => $roles->count(),
            'role_list' => $roles->pluck('slug')->toArray(),
            'has_system_data' => $permissions->where('is_system', true)->count() > 0 ||
                $roles->where('is_system', true)->count() > 0
        ];
    }
}
