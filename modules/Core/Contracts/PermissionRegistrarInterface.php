<?php

namespace Modules\Core\Contracts;

use Modules\Core\Models\Role;

interface PermissionRegistrarInterface
{
    /**
     * Register permissions for a module
     */
    public function registerPermissions(string $module, array $permissions): void;

    /**
     * Get all permissions for a module
     */
    public function getModulePermissions(string $module): array;

    /**
     * Create role with permissions
     */
    public function createRoleWithPermissions(string $module, array $roleData): Role;

    /**
     * Clear permission cache
     */
    public function clearCache(): void;

    /**
     * Get cached permissions
     */
    public function getCachedPermissions(): array;

    /**
     * Sync module permissions
     */
    public function syncModulePermissions(string $module, array $permissions): void;
}
