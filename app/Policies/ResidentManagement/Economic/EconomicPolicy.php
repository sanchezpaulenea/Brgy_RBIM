<?php

namespace App\Policies\ResidentManagement\Economic;

use App\Models\ResidentManagement\Economic\Economic;
use App\Models\UserManagement\User;

class EconomicPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('economic.view');
    }

    public function view(User $user, Economic $economic): bool
    {
        return $user->isAdmin() || $user->hasPermission('economic.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('economic.create');
    }

    public function update(User $user, Economic $economic): bool
    {
        return $user->isAdmin() || $user->hasPermission('economic.update');
    }
}
