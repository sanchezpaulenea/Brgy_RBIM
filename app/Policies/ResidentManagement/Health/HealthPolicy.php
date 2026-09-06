<?php

namespace App\Policies\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\Health;
use App\Models\UserManagement\User;

class HealthPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('health.view');
    }

    public function view(User $user, Health $health): bool
    {
        return $user->isAdmin() || $user->hasPermission('health.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('health.create');
    }

    public function update(User $user, Health $health): bool
    {
        return $user->isAdmin() || $user->hasPermission('health.update');
    }
}
