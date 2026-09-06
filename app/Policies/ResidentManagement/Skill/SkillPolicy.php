<?php

namespace App\Policies\ResidentManagement\Skill;

use App\Models\ResidentManagement\Skill\SkillsDevelopment;
use App\Models\UserManagement\User;

class SkillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('skills.view');
    }

    public function view(User $user, SkillsDevelopment $skillsDevelopment): bool
    {
        return $user->isAdmin() || $user->hasPermission('skills.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('skills.create');
    }

    public function update(User $user, SkillsDevelopment $skillsDevelopment): bool
    {
        return $user->isAdmin() || $user->hasPermission('skills.update');
    }
}
