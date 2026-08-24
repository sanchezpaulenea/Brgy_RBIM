<?php

namespace App\Policies\HouseholdManagement;

use App\Models\UserManagement\User;

class HouseholdPolicy
{
    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('household.create');
    }
}
