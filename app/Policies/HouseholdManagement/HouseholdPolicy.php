<?php

namespace App\Policies\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use App\Models\UserManagement\User;

class HouseholdPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('household.view');
    }

    public function view(User $user, Household $household): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('household.view');
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('household.create');
    }

    public function update(User $user, Household $household): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('household.update');
    }
}
