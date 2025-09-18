<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Modules\Core\Models\Role;
use Modules\Core\Models\UserExtended;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->assignDefaultRoles();
        $this->createSuperAdmin();
    }

    /**
     * Assign roles to existing users
     */
    protected function assignDefaultRoles(): void
    {
        // Make first user Super Admin
        $firstUser = UserExtended::first();
        if ($firstUser) {
            $firstUser->delete();
        }

    }


    /**
     * Create Super Admin User
     */
    protected function createSuperAdmin(): void
    {
        $defaultLanguage = \Modules\Core\Models\Language::query()->default()->first();

        $admin = UserExtended::firstOrCreate(
            ['email' => 'admin@lawoo.local'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'is_super_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
                'language_id' => $defaultLanguage?->id
            ]
        );

        $adminRole = Role::where('slug', 'super-admin')->first();
        if ($adminRole) {
            $admin->assignRole($adminRole);
        }
    }

}
