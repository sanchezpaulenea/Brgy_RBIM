<?php

namespace App\Policies\HouseholdManagement;

use App\Models\HouseholdManagement\HouseholdQuestions;
use App\Models\UserManagement\User;

class HouseholdQuestionsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('household.view');
    }

    public function view(User $user, HouseholdQuestions $questions): bool
    {
        return $user->isAdmin() || $user->hasPermission('household.view');
    }

    public function create(User $user): bool
    {
        return $user->isAdmin()
            || $user->hasPermission('household.create')
            || $user->hasPermission('household.update');
    }

    public function update(User $user, HouseholdQuestions $questions): bool
    {
        return $user->isAdmin() || $user->hasPermission('household.update');
    }
}
