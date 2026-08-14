<?php

namespace App\Policies\BarangayPersonnel;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\UserManagement\User;

class PersonnelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('personnel.view');
    }

    public function create(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('personnel.create');
    }

    public function update(User $user, BarangayPersonnel $personnel): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('personnel.update');
    }
}
