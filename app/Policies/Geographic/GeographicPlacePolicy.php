<?php

namespace App\Policies\Geographic;

use App\Models\UserManagement\User;

/**
 * Province, municipality, and barangay rows are reference data.
 * Any signed-in user may search them. Adding a place is limited to
 * people who encode residents or households. There is no separate
 * permission string for these tables.
 */
class GeographicPlacePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('resident.create')
            || $user->hasPermission('resident.update')
            || $user->hasPermission('household.create')
            || $user->hasPermission('household.update');
    }
}
