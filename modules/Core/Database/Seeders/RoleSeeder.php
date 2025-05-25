<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Role;
use Modules\Core\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSuperAdminRole();
        $this->createAdminRole();
        $this->createManagerRole();
        $this->createUserRole();
    }

    /**
     * Create Super Administrator role
     */
    protected function createSuperAdminRole(): void
    {
        $role = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Administrator',
                'description' => 'Full system access - bypasses all permission checks',
                'module' => 'core',
                'is_system' => true
            ]
        );

        // Super Admin gets ALL permissions
        $allPermissions = Permission::all();
        $role->syncPermissions($allPermissions);
    }

    /**
     * Create Administrator role
     */
    protected function createAdminRole(): void
    {
        $role = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'System administrator with most permissions',
                'module' => 'core',
                'is_system' => true
            ]
        );

        $permissions = [
            'core.dashboard',
            'core.settings',
            'core.modules.view',
            'core.modules.manage',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.roles',
            'system.logs.view',
            'system.cache.clear',
            'system.backup',
            'system.maintenance'
        ];

        $this->assignPermissions($role, $permissions);
    }

    /**
     * Create Manager role
     */
    protected function createManagerRole(): void
    {
        $role = Role::firstOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Management level access',
                'module' => 'core',
                'is_system' => false
            ]
        );

        $permissions = [
            'core.dashboard',
            'users.view',
            'users.create',
            'users.edit',
            'system.logs.view'
        ];

        $this->assignPermissions($role, $permissions);
    }

    /**
     * Create basic User role
     */
    protected function createUserRole(): void
    {
        $role = Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Basic user access',
                'module' => 'core',
                'is_system' => true
            ]
        );

        $permissions = [
            'core.dashboard'
        ];

        $this->assignPermissions($role, $permissions);
    }

    /**
     * Helper method to assign permissions to role
     */
    protected function assignPermissions(Role $role, array $permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->get();
        $role->syncPermissions($permissions);
    }
}
