<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemAdministrationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'retention.view',
            'retention.update',
            'retention.archive',
            'backup.export',
            'backup.restore',
        ];

        foreach ($permissions as $permission) {
            $exists = DB::table('permission')->where('permission', $permission)->exists();

            if (! $exists) {
                DB::table('permission')->insert([
                    'permission' => $permission,
                ]);
            }
        }

        $adminId = DB::table('role')->where('role_name', 'Admin')->value('role_id');
        $superAdminId = DB::table('role')->where('role_name', 'Super Admin')->value('role_id');

        $assignments = [
            'retention.view' => [$adminId, $superAdminId],
            'retention.update' => [$adminId, $superAdminId],
            'retention.archive' => [$adminId, $superAdminId],
            'backup.export' => [$adminId, $superAdminId],
            'backup.restore' => [$superAdminId],
        ];

        foreach ($assignments as $permission => $roleIds) {
            $permissionId = DB::table('permission')->where('permission', $permission)->value('permission_id');

            if ($permissionId === null) {
                continue;
            }

            foreach ($roleIds as $roleId) {
                if ($roleId === null) {
                    continue;
                }

                $assigned = DB::table('role_permission')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if ($assigned) {
                    continue;
                }

                DB::table('role_permission')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        if (
            DB::table('system_setting')->exists()
            && ! DB::table('system_setting')->where('setting_key', 'user_log_retention_days')->exists()
        ) {
            DB::table('system_setting')->insert([
                'setting_key' => 'user_log_retention_days',
                'setting_value' => '365',
                'data_type' => 'int',
                'description' => 'User log retention days',
            ]);
        }
    }
}
