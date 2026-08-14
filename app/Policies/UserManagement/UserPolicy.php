<?php

namespace App\Policies\UserManagement;

use App\Models\UserManagement\User;

/**
 * Authorizes user account management actions.
 * Registered in AuthServiceProvider.
 */
class UserPolicy
{
    /**
     * List all user accounts.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('user.view');
    }

    /**
     * View a single user account.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('user.view');
    }

    /**
     * Create a new user account.
     */
    public function create(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('user.create');
    }

    /**
     * Change a user's account status.
     */
    public function updateStatus(User $user, User $model): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('user.updatestatus');
    }

    /**
     * Reset a user's password to the system default.
     */
    public function resetPassword(User $user, User $model): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('user.resetpassword');
    }

    /**
     * Permanently delete a user account.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('user.delete');
    }

    /**
     * Change the authenticated user's own password.
     */
    public function changePassword(User $user): bool
    {
        return $user->hasPermission('user.changepassword');
    }

    /**
     * End the authenticated user's own session.
     */
    public function logout(User $user): bool
    {
        return true;
    }
}
