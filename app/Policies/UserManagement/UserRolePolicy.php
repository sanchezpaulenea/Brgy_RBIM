<?php

namespace App\Policies\UserManagement;

use App\Models\UserManagement\User;
use App\Models\UserManagement\UserRole;

/**
 * Authorizes user role assignment actions.
 * Registered in AuthServiceProvider.
 */
class UserRolePolicy
{
    /**
     * List role assignments for a user.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('userrole.view');
    }

    /**
     * Assign a role to a user.
     */
    public function create(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('userrole.create');
    }

    /**
     * Enable or disable a role assignment.
     */
    public function updateStatus(User $user, UserRole $userRole): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('userrole.updatestatus');
    }
}
