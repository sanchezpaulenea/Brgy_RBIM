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
            ['permission_id' => 18, 'permission' => 'auditlog.view'],
            ['permission_id' => 19, 'permission' => 'street.view'],
            ['permission_id' => 20, 'permission' => 'street.create'],
            ['permission_id' => 21, 'permission' => 'street.delete'],
            ['permission_id' => 22, 'permission' => 'household.view'],
            ['permission_id' => 23, 'permission' => 'household.create'],
            ['permission_id' => 24, 'permission' => 'household.update'],
            ['permission_id' => 25, 'permission' => 'householdassessment.view'],
            ['permission_id' => 26, 'permission' => 'householdassessment.create'],
            ['permission_id' => 27, 'permission' => 'nationality.view'],
            ['permission_id' => 28, 'permission' => 'nationality.create'],
            ['permission_id' => 29, 'permission' => 'nationality.delete'],
            ['permission_id' => 30, 'permission' => 'ethnicity.view'],
            ['permission_id' => 31, 'permission' => 'ethnicity.create'],
            ['permission_id' => 32, 'permission' => 'ethnicity.delete'],
            ['permission_id' => 33, 'permission' => 'resident.view'],
            ['permission_id' => 34, 'permission' => 'resident.create'],
            ['permission_id' => 35, 'permission' => 'resident.update'],
            ['permission_id' => 36, 'permission' => 'religion.view'],
            ['permission_id' => 37, 'permission' => 'religion.create'],
            ['permission_id' => 38, 'permission' => 'religion.delete'],
            ['permission_id' => 39, 'permission' => 'householdassessment.updatestatus'],
        ], ['permission_id'], ['permission']);
    }
}
