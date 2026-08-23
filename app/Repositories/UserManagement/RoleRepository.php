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

    /**
     * @return Collection<int, Role>
     */
    public function allWithPermissions(): Collection
    {
        return Role::query()
            ->with(['permissions' => fn ($query) => $query->orderBy('permission')])
            ->orderBy('role_id')
            ->get();
    }

    public function findById(int $roleId): ?Role
    {
        return Role::query()
            ->where('role_id', $roleId)
            ->first();
    }
}
