<?php

namespace App\Policies\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Nationality;
use App\Models\UserManagement\User;

class NationalityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->hasPermission('nationality.view')
            || $user->canListResidentReferenceData();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('nationality.create');
    }

    public function delete(User $user, Nationality $nationality): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('nationality.delete');
    }
}
