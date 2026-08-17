<?php

namespace App\Policies\BarangayPersonnel;

use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\UserManagement\User;

class BarangayPersonnelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('pposition.view');
    }

    public function create(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('pposition.create');
    }

    public function delete(User $user, PersonnelPosition $position): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('pposition.delete');
    }
}
