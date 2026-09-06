<?php

namespace App\Policies\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\WomenHealth;
use App\Models\UserManagement\User;

class WomenHealthPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('womenhealth.view');
    }

    public function view(User $user, WomenHealth $womenHealth): bool
    {
        return $user->isAdmin() || $user->hasPermission('womenhealth.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('womanhealth.create');
    }

    public function update(User $user, WomenHealth $womenHealth): bool
    {
        return $user->isAdmin() || $user->hasPermission('womanhealth.update');
    }
}
