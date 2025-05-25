<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createCorePermissions();
        $this->createUserManagementPermissions();
        $this->createSystemPermissions();
    }

    /**
     * Create core system permissions
     */
    protected function createCorePermissions(): void
    {
        $permissions = [
            [
                'name' => 'Access Dashboard',
                'slug' => 'core.dashboard',
                'description' => 'Can access main dashboard',
                'module' => 'core',
                'resource' => 'dashboard',
                'action' => 'view',
                'is_system' => true
            ],
            [
                'name' => 'System Settings',
                'slug' => 'core.settings',
                'description' => 'Can access system settings',
                'module' => 'core',
                'resource' => 'settings',
                'action' => 'manage',
                'is_system' => true
            ],
            [
                'name' => 'View Modules',
                'slug' => 'core.modules.view',
                'description' => 'Can view installed modules',
                'module' => 'core',
                'resource' => 'modules',
                'action' => 'view',
                'is_system' => true
            ],
            [
                'name' => 'Manage Modules',
                'slug' => 'core.modules.manage',
                'description' => 'Can install/uninstall modules',
                'module' => 'core',
                'resource' => 'modules',
                'action' => 'manage',
                'is_system' => true
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }

    /**
     * Create user management permissions
     */
    protected function createUserManagementPermissions(): void
    {
        $permissions = [
            [
                'name' => 'View Users',
                'slug' => 'users.view',
                'description' => 'Can view user list and profiles',
                'module' => 'core',
                'resource' => 'users',
                'action' => 'view',
                'is_system' => true
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users.create',
                'description' => 'Can create new users',
                'module' => 'core',
                'resource' => 'users',
                'action' => 'create',
                'is_system' => true
            ],
            [
                'name' => 'Edit Users',
                'slug' => 'users.edit',
                'description' => 'Can edit user information',
                'module' => 'core',
                'resource' => 'users',
                'action' => 'edit',
                'is_system' => true
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users.delete',
                'description' => 'Can delete users',
                'module' => 'core',
                'resource' => 'users',
                'action' => 'delete',
                'is_system' => true
            ],
            [
                'name' => 'Manage User Roles',
                'slug' => 'users.roles',
                'description' => 'Can assign/remove user roles',
                'module' => 'core',
                'resource' => 'users',
                'action' => 'manage_roles',
                'is_system' => true
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }

    /**
     * Create system administration permissions
     */
    protected function createSystemPermissions(): void
    {
        $permissions = [
            [
                'name' => 'View Logs',
                'slug' => 'system.logs.view',
                'description' => 'Can view system logs',
                'module' => 'core',
                'resource' => 'logs',
                'action' => 'view',
                'is_system' => true
            ],
            [
                'name' => 'Clear Cache',
                'slug' => 'system.cache.clear',
                'description' => 'Can clear system cache',
                'module' => 'core',
                'resource' => 'cache',
                'action' => 'clear',
                'is_system' => true
            ],
            [
                'name' => 'Database Backup',
                'slug' => 'system.backup',
                'description' => 'Can create database backups',
                'module' => 'core',
                'resource' => 'backup',
                'action' => 'create',
                'is_system' => true
            ],
            [
                'name' => 'System Maintenance',
                'slug' => 'system.maintenance',
                'description' => 'Can put system in maintenance mode',
                'module' => 'core',
                'resource' => 'maintenance',
                'action' => 'manage',
                'is_system' => true
            ]
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
