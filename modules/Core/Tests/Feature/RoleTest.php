<?php

namespace Modules\Core\Tests\Feature;

use Tests\TestCase;
use Modules\Core\Models\Role;
use Modules\Core\Models\Permission;
use Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_role_can_be_created()
    {
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'module' => 'test',
            'description' => 'A test role'
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'Test Role',
            'slug' => 'test-role',
            'module' => 'test'
        ]);
    }

    public function test_role_can_have_permissions()
    {
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'module' => 'test'
        ]);

        $permission = Permission::create([
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'module' => 'test'
        ]);

        $role->givePermission($permission);

        $this->assertTrue($role->hasPermission($permission));
        $this->assertTrue($role->hasPermission('test.permission'));
        $this->assertCount(1, $role->permissions);
    }

    public function test_role_permission_management()
    {
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'module' => 'test'
        ]);

        $permission1 = Permission::create([
            'name' => 'Permission 1',
            'slug' => 'test.permission1',
            'module' => 'test'
        ]);

        $permission2 = Permission::create([
            'name' => 'Permission 2',
            'slug' => 'test.permission2',
            'module' => 'test'
        ]);

        // Test give permission
        $role->givePermission($permission1);
        $this->assertTrue($role->hasPermission($permission1));

        // Test sync permissions
        $role->syncPermissions([$permission1, $permission2]);
        $this->assertTrue($role->hasAllPermissions(['test.permission1', 'test.permission2']));

        // Test revoke permission
        $role->revokePermission($permission1);
        $this->assertFalse($role->hasPermission($permission1));
        $this->assertTrue($role->hasPermission($permission2));
    }
}
