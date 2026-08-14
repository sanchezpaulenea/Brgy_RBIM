<?php

namespace App\Policies;

use App\Enums\LookupType;
use App\Models\Lookup\Lookup;
use App\Models\UserManagement\User;

class LookupPolicy
{
    /**
     * Any authenticated user may view lookup tables.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Only personnel-position supports create, gated by pposition.create.
     */
    public function create(User $user, Lookup $lookup, string $type): bool
    {
        return $type === LookupType::PersonnelPosition->value
            && $user->hasPermission('pposition.create');
    }

    /**
     * Only personnel-position supports delete, gated by pposition.delete.
     */
    public function delete(User $user, Lookup $lookup, string $type): bool
    {
        return $type === LookupType::PersonnelPosition->value
            && $user->hasPermission('pposition.delete');
    }
}