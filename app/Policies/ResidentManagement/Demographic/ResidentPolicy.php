<?php

namespace App\Policies\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;

class ResidentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('resident.view');
    }

    public function view(User $user, Resident $resident): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('resident.view');
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('resident.create');
    }

    public function update(User $user, Resident $resident): bool
    {
        return $user->isSuperAdmin() || $user->hasPermission('resident.update');
    }
}
