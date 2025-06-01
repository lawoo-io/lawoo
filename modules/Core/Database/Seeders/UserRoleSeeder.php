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
//        $this->createDemoUsers();
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
//            $firstUser->update([
//                'is_super_admin' => true,
//                'is_active' => true
//            ]);
//
//            $superAdminRole = Role::where('slug', 'super-admin')->first();
//            if ($superAdminRole) {
//                $firstUser->assignRole($superAdminRole);
//            }
        }

        // Assign basic user role to other users
//        $otherUsers = UserExtended::where('id', '>', 1)->get();
//        $userRole = Role::where('slug', 'user')->first();
//
//        if ($userRole) {
//            foreach ($otherUsers as $user) {
//                $user->update(['is_active' => true]);
//                $user->assignRole($userRole);
//            }
//        }
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

    /**
     * Create demo users for testing
     */
    protected function createDemoUsers(): void
    {

        // Create Manager User
        $manager = UserExtended::firstOrCreate(
            ['email' => 'manager@lawoo.local'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now()
            ]
        );

        $managerRole = Role::where('slug', 'manager')->first();
        if ($managerRole) {
            $manager->assignRole($managerRole);
        }

        // Create Basic User
        $user = UserExtended::firstOrCreate(
            ['email' => 'user@lawoo.local'],
            [
                'name' => 'Basic User',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now()
            ]
        );

        $userRole = Role::where('slug', 'user')->first();
        if ($userRole) {
            $user->assignRole($userRole);
        }
    }
}
