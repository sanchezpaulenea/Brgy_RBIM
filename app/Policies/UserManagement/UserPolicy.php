<?php

namespace App\Policies\UserManagement;

use App\Models\UserManagement\User;

/**
 * Authorizes actions a User can perform on their own account.
 * Registered in AuthServiceProvider.
 */
class UserPolicy
{
    /**
     * Any authenticated user who holds the `user.changepassword` permission
     * may change their own password.
     */
    public function changePassword(User $user): bool
    {
        return $user->hasPermission('user.changepassword');
    }
}
