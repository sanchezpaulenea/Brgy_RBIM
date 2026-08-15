<?php

namespace App\Repositories\UserManagement;

use App\Models\UserManagement\Role;
use App\Repositories\Interfaces\UserManagement\RoleInterface;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository implements RoleInterface
{
    /**
     * @return Collection<int, Role>
     */
    public function all(): Collection
    {
        return Role::query()->orderBy('role_name')->get();
    }
}
