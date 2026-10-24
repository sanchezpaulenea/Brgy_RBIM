<?php

use Database\Seeders\SystemAdministrationPermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Existing databases are loaded from the SQL dump, so this runs the same
     * grant the seeder uses without requiring a re-seed.
     */
    public function up(): void
    {
        (new SystemAdministrationPermissionSeeder)->run();
    }

    public function down(): void
    {
        $permissions = [
            'retention.view',
            'retention.update',
            'retention.archive',
            'backup.export',
            'backup.restore',
        ];

        $ids = DB::table('permission')->whereIn('permission', $permissions)->pluck('permission_id');

        if ($ids->isNotEmpty()) {
            DB::table('role_permission')->whereIn('permission_id', $ids)->delete();
            DB::table('permission')->whereIn('permission_id', $ids)->delete();
        }
    }
};
