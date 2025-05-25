<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\Role;
use Modules\Core\Models\Permission;
use Modules\Core\Contracts\PermissionRegistrarInterface;

class RBACCleanupService
{
    protected PermissionRegistrarInterface $registrar;

    public function __construct(PermissionRegistrarInterface $registrar)
    {
        $this->registrar = $registrar;
    }

    /**
     * RBAC-Daten für ein Modul komplett aus DB entfernen
     *
     * @param string $module Module name
     * @param array $options Additional options
     * @return array Cleanup results
     */
    public function removeModuleRBAC(string $module, array $options = []): array
    {
        $module = strtolower($module);
        $options = array_merge([
            'force_system' => false,  // Auch System-Permissions/Roles löschen
            'dry_run' => false        // Nur simulieren
        ], $options);

        $result = [
            'module' => $module,
            'permissions_removed' => 0,
            'roles_removed' => 0,
            'user_assignments_removed' => 0,
            'success' => false
        ];

        try {
            if ($options['dry_run']) {
                return $this->simulateRemoval($module, $options['force_system']);
            }

            DB::transaction(function () use ($module, $options, &$result) {
                // 1. User-Role Assignments entfernen
                $result['user_assignments_removed'] = $this->removeUserRoleAssignments($module, $options['force_system']);

                // 2. Rollen entfernen (inkl. Role-Permission Zuweisungen)
                $result['roles_removed'] = $this->removeRoles($module, $options['force_system']);

                // 3. Permissions entfernen
                $result['permissions_removed'] = $this->removePermissions($module, $options['force_system']);

                // 4. Cache leeren
                $this->registrar->clearCache();
            });

            $result['success'] = true;
            Log::info("RBAC cleanup completed for module: {$module}", $result);

        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            Log::error("RBAC cleanup failed for module: {$module}", [
                'error' => $e->getMessage()
            ]);
        }

        return $result;
    }

    /**
     * Mehrere Module gleichzeitig bereinigen
     */
    public function removeMultipleModulesRBAC(array $modules, array $options = []): array
    {
        $results = [];

        foreach ($modules as $module) {
            $results[$module] = $this->removeModuleRBAC($module, $options);
        }

        return $results;
    }

    /**
     * Simulation - was würde gelöscht werden
     */
    protected function simulateRemoval(string $module, bool $forceSystem = false): array
    {
        $permissionsQuery = Permission::where('module', $module);
        $rolesQuery = Role::where('module', $module);

        if (!$forceSystem) {
            $permissionsQuery->where('is_system', false);
            $rolesQuery->where('is_system', false);
        }

        $permissions = $permissionsQuery->get();
        $roles = $rolesQuery->get();

        $userAssignments = DB::table('user_roles')
            ->whereIn('role_id', $roles->pluck('id'))
            ->count();

        return [
            'module' => $module,
            'dry_run' => true,
            'permissions_found' => $permissions->count(),
            'permissions_list' => $permissions->pluck('slug')->toArray(),
            'roles_found' => $roles->count(),
            'roles_list' => $roles->pluck('slug')->toArray(),
            'user_assignments_found' => $userAssignments
        ];
    }

    /**
     * User-Role Assignments für Modul entfernen
     */
    protected function removeUserRoleAssignments(string $module, bool $forceSystem = false): int
    {
        $rolesQuery = Role::where('module', $module);

        if (!$forceSystem) {
            $rolesQuery->where('is_system', false);
        }

        $roleIds = $rolesQuery->pluck('id');

        if ($roleIds->isEmpty()) {
            return 0;
        }

        return DB::table('user_roles')
            ->whereIn('role_id', $roleIds)
            ->delete();
    }

    /**
     * Rollen für Modul entfernen
     */
    protected function removeRoles(string $module, bool $forceSystem = false): int
    {
        $rolesQuery = Role::where('module', $module);

        if (!$forceSystem) {
            $rolesQuery->where('is_system', false);
        }

        $roles = $rolesQuery->get();
        $count = 0;

        foreach ($roles as $role) {
            // Role-Permission Zuweisungen entfernen
            DB::table('role_permissions')
                ->where('role_id', $role->id)
                ->delete();

            // Rolle löschen (auch soft deleted)
            $role->forceDelete();
            $count++;
        }

        return $count;
    }

    /**
     * Permissions für Modul entfernen
     */
    protected function removePermissions(string $module, bool $forceSystem = false): int
    {
        $permissionsQuery = Permission::where('module', $module);

        if (!$forceSystem) {
            $permissionsQuery->where('is_system', false);
        }

        $permissions = $permissionsQuery->get();
        $count = 0;

        foreach ($permissions as $permission) {
            // Permission-Role Zuweisungen entfernen (falls noch vorhanden)
            DB::table('role_permissions')
                ->where('permission_id', $permission->id)
                ->delete();

            // Permission löschen
            $permission->delete();
            $count++;
        }

        return $count;
    }
}
