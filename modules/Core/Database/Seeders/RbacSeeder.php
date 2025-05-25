<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reihenfolge ist wichtig!
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UserRoleSeeder::class,
        ]);
    }
}
