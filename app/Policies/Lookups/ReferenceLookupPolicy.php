<?php

namespace App\Policies\Lookups;

use App\Models\UserManagement\User;

/**
 * Seeded reference tables with no create/delete permissions.
 * Any authenticated user may list them.
 */
class ReferenceLookupPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }
}
