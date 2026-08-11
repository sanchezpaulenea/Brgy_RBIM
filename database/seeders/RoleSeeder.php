<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('role')->upsert([
            ['role_id' => 1, 'role_name' => 'Encoder'],
            ['role_id' => 2, 'role_name' => 'Admin'],
            ['role_id' => 3, 'role_name' => 'Super Admin'],
            ['role_id' => 4, 'role_name' => 'Guest'],
        ], ['role_id'], ['role_name']);
    }
}
