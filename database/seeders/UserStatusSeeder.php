<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('user_status')->upsert([
            ['user_status_id' => 1, 'user_status' => 'Active',    'can_login' => 1],
            ['user_status_id' => 2, 'user_status' => 'Disabled',  'can_login' => 0],
            ['user_status_id' => 3, 'user_status' => 'Locked',    'can_login' => 0],
            ['user_status_id' => 4, 'user_status' => 'Suspended', 'can_login' => 0],
        ], ['user_status_id'], ['user_status', 'can_login']);
    }
}
