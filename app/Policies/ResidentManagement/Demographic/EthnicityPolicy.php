<?php

namespace App\Policies\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Ethnicity;
use App\Models\UserManagement\User;

class EthnicityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('ethnicity.view');
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('ethnicity.create');
    }

    public function delete(User $user, Ethnicity $ethnicity): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('ethnicity.delete');
    }
}
