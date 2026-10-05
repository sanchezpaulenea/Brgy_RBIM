<?php

namespace App\Policies\HouseholdManagement;

use App\Models\HouseholdManagement\PetCensus;
use App\Models\UserManagement\User;

class PetCensusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('petcensus.view');
    }

    public function view(User $user, PetCensus $pet): bool
    {
        return $user->hasPermission('petcensus.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('petcensus.create');
    }

    public function update(User $user, PetCensus $pet): bool
    {
        return $user->hasPermission('petcensus.update');
    }
}
