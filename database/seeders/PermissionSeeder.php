<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permission')->upsert([
            ['permission_id' => 1, 'permission' => 'user.create'],
            ['permission_id' => 2, 'permission' => 'user.view'],
            ['permission_id' => 3, 'permission' => 'user.resetpassword'],
            ['permission_id' => 4, 'permission' => 'user.updatestatus'],
            ['permission_id' => 5, 'permission' => 'userlog.view'],
            ['permission_id' => 6, 'permission' => 'userrole.create'],
            ['permission_id' => 7, 'permission' => 'userrole.view'],
            ['permission_id' => 8, 'permission' => 'userrole.updatestatus'],
            ['permission_id' => 9, 'permission' => 'personnel.create'],
            ['permission_id' => 10, 'permission' => 'personnel.view'],
            ['permission_id' => 11, 'permission' => 'personnel.update'],
            ['permission_id' => 12, 'permission' => 'pposition.create'],
            ['permission_id' => 13, 'permission' => 'pposition.view'],
            ['permission_id' => 14, 'permission' => 'pposition.delete'],
            ['permission_id' => 15, 'permission' => 'setting.view'],
            ['permission_id' => 16, 'permission' => 'setting.update'],
            ['permission_id' => 17, 'permission' => 'user.changepassword'],
            ['permission_id' => 19, 'permission' => 'user.delete'],
        ], ['permission_id'], ['permission']);
    }
}
