<?php

namespace App\Policies\Logs;

use App\Models\UserManagement\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('auditlog.view');
    }
}
