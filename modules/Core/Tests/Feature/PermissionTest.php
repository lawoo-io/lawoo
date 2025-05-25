<?php

namespace Modules\Core\Tests\Feature;

use Tests\TestCase;
use Modules\Core\Models\Permission;
use Modules\Core\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_permission_can_be_created()
    {
        $permission = Permission::create([
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'module' => 'test',
            'resource' => 'posts',
            'action' => 'create'
        ]);

        $this->assertDatabaseHas('permissions', [
            'name' => 'Test Permission',
            'slug' => 'test.permission',
            'module' => 'test'
        ]);
    }

    public function test_permission_scopes()
    {
        Permission::create([
            'name' => 'Core Permission',
            'slug' => 'core.test',
            'module' => 'core'
        ]);

        Permission::create([
            'name' => 'Blog Permission',
            'slug' => 'blog.test',
            'module' => 'blog'
        ]);

        $corePermissions = Permission::byModule('core')->get();
        $blogPermissions = Permission::byModule('blog')->get();

        $this->assertCount(1, $corePermissions);
        $this->assertCount(1, $blogPermissions);
        $this->assertEquals('core.test', $corePermissions->first()->slug);
        $this->assertEquals('blog.test', $blogPermissions->first()->slug);
    }

    public function test_permission_belongs_to_roles()
    {
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

        $this->assertCount(1, $permission->roles);
        $this->assertEquals('test-role', $permission->roles->first()->slug);
    }
}
