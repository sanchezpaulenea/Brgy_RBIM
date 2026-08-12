<?php

namespace App\Policies\Authentication;

use App\Models\UserManagement\User;

class UserLogPolicy
{
    /**
     * View session and login history.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('userlog.view');
    }
}
