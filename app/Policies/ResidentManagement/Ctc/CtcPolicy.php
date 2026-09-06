<?php

namespace App\Policies\ResidentManagement\Ctc;

use App\Models\ResidentManagement\Ctc\Ctc;
use App\Models\UserManagement\User;

class CtcPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('ctc.view');
    }

    public function view(User $user, Ctc $ctc): bool
    {
        return $user->isAdmin() || $user->hasPermission('ctc.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('ctc.create');
    }

    public function update(User $user, Ctc $ctc): bool
    {
        return $user->isAdmin() || $user->hasPermission('ctc.update');
    }
}
