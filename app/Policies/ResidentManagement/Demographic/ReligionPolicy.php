<?php

namespace App\Policies\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Religion;
use App\Models\UserManagement\User;

class ReligionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('religion.view')
            || $user->canListResidentReferenceData();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('religion.create');
    }

    public function delete(User $user, Religion $religion): bool
    {
        return $user->hasPermission('religion.delete');
    }
}
