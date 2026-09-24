<?php

namespace App\Policies\HouseholdManagement;

use App\Models\UserManagement\User;

class PetLookupWritePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin()
            || $user->hasPermission('household.create')
            || $user->hasPermission('household.update');
    }
}
