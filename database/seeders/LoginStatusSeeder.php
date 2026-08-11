<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LoginStatusSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('login_status')->upsert([
            ['login_status_id' => 1, 'login_status' => 'Success'],
            ['login_status_id' => 2, 'login_status' => 'Invalid Password'],
            ['login_status_id' => 3, 'login_status' => 'Invalid Username'],
            ['login_status_id' => 4, 'login_status' => 'Invalid Credentials'],
            ['login_status_id' => 5, 'login_status' => 'Account Locked'],
        ], ['login_status_id'], ['login_status']);
    }
}
