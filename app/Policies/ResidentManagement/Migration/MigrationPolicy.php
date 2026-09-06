<?php

namespace App\Policies\ResidentManagement\Migration;

use App\Models\ResidentManagement\Migration\Migration;
use App\Models\UserManagement\User;

class MigrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasPermission('migration.view');
    }

    public function view(User $user, Migration $migration): bool
    {
        return $user->isAdmin() || $user->hasPermission('migration.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('migration.create');
    }

    public function update(User $user, Migration $migration): bool
    {
        return $user->isAdmin() || $user->hasPermission('migration.update');
    }
}
