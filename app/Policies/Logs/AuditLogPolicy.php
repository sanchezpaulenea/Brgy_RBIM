<?php

namespace App\Policies\Logs;

use App\Models\UserManagement\User;

class AuditLogPolicy
{
    /**
     * The `permission` table has no `auditlog.view` row, so access is granted by
     * role: Admin and Super Admin only.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSystemAdministrator();
    }
}
