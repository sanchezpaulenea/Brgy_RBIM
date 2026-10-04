<?php

namespace App\Policies\HouseholdManagement;

use App\Models\HouseholdManagement\PetCensus;
use App\Models\UserManagement\User;

class PetCensusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('pet.view');
    }

    public function view(User $user, PetCensus $pet): bool
    {
        return $user->hasPermission('pet.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('pet.create');
    }

    public function update(User $user, PetCensus $pet): bool
    {
        return $user->hasPermission('pet.update');
    }
}
