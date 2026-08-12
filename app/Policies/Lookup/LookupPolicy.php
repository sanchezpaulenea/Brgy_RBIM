<?php

namespace App\Policies\Lookup;

use App\Models\Lookup\Lookup;
use App\Models\UserManagement\User;

/**
 * Authorizes lookup table access.
 * Registered in AuthServiceProvider.
 */
class LookupPolicy
{
    public const PERSONNEL_POSITION = 'personnel-position';

    /**
     * List lookup values for any supported lookup type.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Create lookup values — only personnel_position for now.
     */
    public function create(User $user, Lookup $lookup): bool
    {
        if ($lookup->type !== self::PERSONNEL_POSITION) {
            return false;
        }

        return $user->isSystemAdministrator() && $user->hasPermission('pposition.create');
    }

    /**
     * Delete lookup values — only personnel_position for now.
     */
    public function delete(User $user, Lookup $lookup): bool
    {
        if ($lookup->type !== self::PERSONNEL_POSITION) {
            return false;
        }

        return $user->isSystemAdministrator() && $user->hasPermission('pposition.delete');
    }
}
