<?php

namespace App\Policies\ResidentManagement\Skill;

use App\Models\UserManagement\User;

class SkillTypeWritePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin()
            || $user->hasPermission('skills.create')
            || $user->hasPermission('skills.update');
    }
}
