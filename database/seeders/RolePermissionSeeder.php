<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Maps each role to its permission set as defined in brgy_rbim.sql.
         *
         * role_id 1 = Encoder      → user.changepassword only
         * role_id 2 = Admin        → setting.view, setting.update, user.changepassword, auditlog.view
         * role_id 3 = Super Admin  → all permissions
         * role_id 4 = Guest        → user.changepassword only
         */
        DB::table('role_permission')->upsert([
            // Encoder
            ['role_permission_id' => 19, 'role_id' => 1, 'permission_id' => 17],
            // Admin
            ['role_permission_id' => 16, 'role_id' => 2, 'permission_id' => 15],
            ['role_permission_id' => 17, 'role_id' => 2, 'permission_id' => 16],
            ['role_permission_id' => 18, 'role_id' => 2, 'permission_id' => 17],
            ['role_permission_id' => 23, 'role_id' => 2, 'permission_id' => 18],
            // Super Admin
            ['role_permission_id' => 7, 'role_id' => 3, 'permission_id' => 1],
            ['role_permission_id' => 10, 'role_id' => 3, 'permission_id' => 2],
            ['role_permission_id' => 8, 'role_id' => 3, 'permission_id' => 3],
            ['role_permission_id' => 9, 'role_id' => 3, 'permission_id' => 4],
            ['role_permission_id' => 11, 'role_id' => 3, 'permission_id' => 5],
            ['role_permission_id' => 12, 'role_id' => 3, 'permission_id' => 6],
            ['role_permission_id' => 14, 'role_id' => 3, 'permission_id' => 7],
            ['role_permission_id' => 13, 'role_id' => 3, 'permission_id' => 8],
            ['role_permission_id' => 1, 'role_id' => 3, 'permission_id' => 9],
            ['role_permission_id' => 3, 'role_id' => 3, 'permission_id' => 10],
            ['role_permission_id' => 2, 'role_id' => 3, 'permission_id' => 11],
            ['role_permission_id' => 4, 'role_id' => 3, 'permission_id' => 12],
            ['role_permission_id' => 6, 'role_id' => 3, 'permission_id' => 13],
            ['role_permission_id' => 5, 'role_id' => 3, 'permission_id' => 14],
            ['role_permission_id' => 20, 'role_id' => 3, 'permission_id' => 17],
            ['role_permission_id' => 22, 'role_id' => 3, 'permission_id' => 18],
            ['role_permission_id' => 24, 'role_id' => 3, 'permission_id' => 19],
            // Guest
            ['role_permission_id' => 21, 'role_id' => 4, 'permission_id' => 17],
        ], ['role_permission_id'], ['role_id', 'permission_id']);
    }
}
