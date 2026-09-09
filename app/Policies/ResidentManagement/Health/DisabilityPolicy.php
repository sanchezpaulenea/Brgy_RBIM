<?php

namespace App\Policies\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\Disability;
use App\Models\UserManagement\User;

class DisabilityPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin()
            || $user->hasPermission('health.create')
            || $user->hasPermission('health.update')
            || $user->canListResidentReferenceData();
    }

    public function delete(User $user, Disability $disability): bool
    {
        return false;
    }
}
