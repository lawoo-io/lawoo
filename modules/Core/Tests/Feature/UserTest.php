<?php

namespace Modules\Core\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Core\Models\Role;
use Modules\Core\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->artisan('migrate');
    }

    public function test_user_model_has_rbac_methods()
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_super_admin' => false
        ]);

        // Verify that User has RBAC methods from HasRoles trait
        $this->assertTrue(method_exists($user, 'hasRole'));
        $this->assertTrue(method_exists($user, 'assignRole'));
        $this->assertTrue(method_exists($user, 'hasPermissionViaRole'));
        $this->assertTrue(method_exists($user, 'isSuperAdmin'));
        $this->assertTrue(method_exists($user, 'isActive'));
    }

    public function test_user_can_be_assigned_roles()
    {
        $user = User::factory()->create([
            'is_active' => true
        ]);

        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'module' => 'test',
            'is_system' => false
        ]);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('test-role'));
        $this->assertEquals('Test Role', $user->getRoleNames());
        $this->assertCount(1, $user->roles);
    }

    public function test_user_can_be_assigned_multiple_roles()
    {
        $user = User::factory()->create(['is_active' => true]);

        $role1 = Role::create([
            'name' => 'Role One',
            'slug' => 'role-one',
            'module' => 'test'
        ]);

        $role2 = Role::create([
            'name' => 'Role Two',
            'slug' => 'role-two',
            'module' => 'test'
        ]);

        $user->assignRole($role1);
        $user->assignRole($role2);

        $this->assertTrue($user->hasRole('role-one'));
        $this->assertTrue($user->hasRole('role-two'));
        $this->assertTrue($user->hasAnyRole(['role-one', 'role-two']));
        $this->assertTrue($user->hasAllRoles(['role-one', 'role-two']));
        $this->assertCount(2, $user->roles);
    }

    public function test_user_permissions_work_through_roles()
    {
        $user = User::factory()->create(['is_active' => true]);

        $permission = Permission::create([
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'module' => 'test',
            'resource' => 'test',
            'action' => 'view'
        ]);

        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'module' => 'test'
        ]);

        $role->givePermission($permission);
        $user->assignRole($role);

        $this->assertTrue($user->can('test.permission'));
        $this->assertFalse($user->cannot('test.permission'));
        $this->assertTrue($user->hasPermissionViaRole('test.permission'));
    }

    public function test_user_can_have_multiple_permissions()
    {
        $user = User::factory()->create(['is_active' => true]);

        $permission1 = Permission::create([
            'name' => 'Create Posts',
            'slug' => 'posts.create',
            'module' => 'blog'
        ]);

        $permission2 = Permission::create([
            'name' => 'Edit Posts',
            'slug' => 'posts.edit',
            'module' => 'blog'
        ]);

        $role = Role::create([
            'name' => 'Blog Editor',
            'slug' => 'blog-editor',
            'module' => 'blog'
        ]);

        $role->givePermission($permission1);
        $role->givePermission($permission2);
        $user->assignRole($role);

        $this->assertTrue($user->can('posts.create'));
        $this->assertTrue($user->can('posts.edit'));
        $this->assertCount(2, $user->getAllPermissions());
    }

    public function test_super_admin_bypasses_all_permissions()
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'is_active' => true
        ]);

        // Super admin can do anything, even non-existent permissions
        $this->assertTrue($user->can('any.random.permission'));
        $this->assertTrue($user->can('nonexistent.permission'));
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->canBypassPermissions());
    }

    public function test_inactive_user_cannot_access_anything()
    {
        $user = User::factory()->create([
            'is_active' => false,
            'is_super_admin' => false
        ]);

        $permission = Permission::create([
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'module' => 'test'
        ]);

        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'module' => 'test'
        ]);

        $role->givePermission($permission);
        $user->assignRole($role);

        // Even with role and permission, inactive user cannot access
        $this->assertFalse($user->can('test.permission'));
        $this->assertTrue($user->cannot('test.permission'));
        $this->assertFalse($user->isActive());
    }

    public function test_user_role_management()
    {
        $user = User::factory()->create(['is_active' => true]);

        $adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'module' => 'core'
        ]);

        $userRole = Role::create([
            'name' => 'User',
            'slug' => 'user',
            'module' => 'core'
        ]);

        // Assign roles
        $user->assignRole($adminRole);
        $user->assignRole($userRole);

        // Check role assignment
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));

        // Remove role
        $user->removeRole($adminRole);
        $this->assertFalse($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('user'));

        // Sync roles
        $user->syncRoles(['admin']);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user'));
    }

    public function test_user_activation_deactivation()
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->assertFalse($user->isActive());

        // Activate user
        $user->activate();
        $this->assertTrue($user->fresh()->isActive());

        // Deactivate user
        $user->deactivate();
        $this->assertFalse($user->fresh()->isActive());
    }

    public function test_user_super_admin_management()
    {
        $user = User::factory()->create(['is_super_admin' => false]);

        $this->assertFalse($user->isSuperAdmin());

        // Make super admin
        $user->makeSuperAdmin();
        $this->assertTrue($user->fresh()->isSuperAdmin());

        // Remove super admin
        $user->removeSuperAdmin();
        $this->assertFalse($user->fresh()->isSuperAdmin());
    }

    public function test_user_permission_count()
    {
        $user = User::factory()->create(['is_active' => true]);

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

        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'module' => 'test'
        ]);

        $role->syncPermissions([$permission1, $permission2]);
        $user->assignRole($role);

        $this->assertEquals(2, $user->getPermissionCount());
        $this->assertContains('test.permission1', $user->getPermissionsList());
        $this->assertContains('test.permission2', $user->getPermissionsList());
    }

    public function test_user_admin_and_manager_checks()
    {
        // Test admin detection
        $adminUser = User::factory()->create(['is_active' => true]);
        $adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'module' => 'core'
        ]);
        $adminUser->assignRole($adminRole);
        $this->assertTrue($adminUser->isAdmin());

        // Test manager detection
        $managerUser = User::factory()->create(['is_active' => true]);
        $managerRole = Role::create([
            'name' => 'Sales Manager',
            'slug' => 'sales-manager',
            'module' => 'sales'
        ]);
        $managerUser->assignRole($managerRole);
        $this->assertTrue($managerUser->isManager());

        // Test regular user
        $regularUser = User::factory()->create(['is_active' => true]);
        $userRole = Role::create([
            'name' => 'Regular User',
            'slug' => 'user',
            'module' => 'core'
        ]);
        $regularUser->assignRole($userRole);
        $this->assertFalse($regularUser->isAdmin());
        $this->assertFalse($regularUser->isManager());
    }
}
