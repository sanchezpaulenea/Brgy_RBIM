<?php

namespace App\Policies\HouseholdManagement;

use App\Models\HouseholdManagement\Street;
use App\Models\UserManagement\User;

class StreetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasPermission('street.view')
            || $user->canListResidentReferenceData();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('street.create');
    }

    public function delete(User $user, Street $street): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('street.delete');
    }
}
