<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with all reference data from brgy_rbim.sql.
     */
    public function run(): void
    {
        $this->call([
            UserStatusSeeder::class,
            LoginStatusSeeder::class,
            ActionSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            SystemSettingSeeder::class,
        ]);
    }
}
