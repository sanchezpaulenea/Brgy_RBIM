<?php

namespace App\Policies\AuditLog;

use App\Models\UserManagement\User;

/**
 * Authorizes audit log viewing.
 * Reuses userlog.view — no separate audit-log permission exists yet.
 */
class AuditLogPolicy
{
    /**
     * List audit log entries (general CRUD actions).
     */
    public function viewAny(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('userlog.view');
    }
}
