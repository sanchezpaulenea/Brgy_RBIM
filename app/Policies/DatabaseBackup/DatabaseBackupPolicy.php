<?php

namespace App\Policies\DatabaseBackup;

use App\Models\UserManagement\User;

class DatabaseBackupPolicy
{
    /**
     * Download a SQL, Excel, or PDF copy of the database.
     */
    public function export(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('backup.export');
    }

    /**
     * Replace current records with a SQL backup created by this system.
     * Super Admin only, even when the permission is granted to another role.
     */
    public function restore(User $user): bool
    {
        return $user->isSuperAdmin() && $user->hasPermission('backup.restore');
    }
}
