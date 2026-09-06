<?php

namespace App\Policies\ResidentManagement\Education;

use App\Models\ResidentManagement\Education\Education;
use App\Models\UserManagement\User;

class EducationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('education.view');
    }

    public function view(User $user, Education $education): bool
    {
        return $user->isAdmin() || $user->hasPermission('education.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('education.create');
    }

    public function update(User $user, Education $education): bool
    {
        return $user->isAdmin() || $user->hasPermission('edcuation.update');
    }
}
