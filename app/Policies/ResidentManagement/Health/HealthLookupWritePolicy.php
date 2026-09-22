<?php

namespace App\Policies\ResidentManagement\Health;

use App\Models\UserManagement\User;

class HealthLookupWritePolicy
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

    public function delete(User $user): bool
    {
        return false;
    }
}
