<?php

namespace App\Policies\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\InfantHealth;
use App\Models\UserManagement\User;

class InfantHealthPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('infanthealth.view');
    }

    public function view(User $user, InfantHealth $infantHealth): bool
    {
        return $user->isAdmin() || $user->hasPermission('infanthealth.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('infanthealth.create');
    }

    public function update(User $user, InfantHealth $infantHealth): bool
    {
        return $user->isAdmin() || $user->hasPermission('infanthealth.update');
    }
}
