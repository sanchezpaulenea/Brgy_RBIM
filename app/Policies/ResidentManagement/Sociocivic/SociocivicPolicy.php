<?php

namespace App\Policies\ResidentManagement\Sociocivic;

use App\Models\ResidentManagement\Sociocivic\Sociocivic;
use App\Models\UserManagement\User;

class SociocivicPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('sociocivic.view');
    }

    public function view(User $user, Sociocivic $sociocivic): bool
    {
        return $user->isAdmin() || $user->hasPermission('sociocivic.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('sociocivic.create');
    }

    public function update(User $user, Sociocivic $sociocivic): bool
    {
        return $user->isAdmin() || $user->hasPermission('sociocivic.update');
    }
}
