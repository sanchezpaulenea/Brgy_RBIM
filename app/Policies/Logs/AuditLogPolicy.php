<?php

namespace App\Policies\Logs;

use App\Models\UserManagement\User;

class AuditLogPolicy
{
    /**
     * View audit history. Separate from session/login history (`userlog.view`).
     */
    public function viewAny(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('auditlog.view');
    }
}
