<?php

namespace App\Policies\HouseholdManagement;

use App\Models\HouseholdManagement\HouseholdAssessment;
use App\Models\UserManagement\User;

class HouseholdAssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('householdassessment.view');
    }

    public function view(User $user, HouseholdAssessment $assessment): bool
    {
        return $user->isAdmin() || $user->hasPermission('householdassessment.view');
    }

    public function create(User $user): bool
    {
        return ($user->isEncoder() || $user->isAdmin()) && $user->hasPermission('householdassessment.create');
    }
}
