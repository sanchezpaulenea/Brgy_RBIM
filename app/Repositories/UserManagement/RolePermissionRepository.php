<?php

namespace App\Repositories\UserManagement;

use App\Models\UserManagement\RolePermission;
use App\Repositories\Interfaces\UserManagement\RolePermissionInterface;
use Illuminate\Database\Eloquent\Collection;

class RolePermissionRepository implements RolePermissionInterface
{
    /**
     * @return Collection<int, RolePermission>
     */
    public function all(): Collection
    {
        return RolePermission::query()
            ->with(['role', 'permission'])
            ->orderBy('role_permission_id')
            ->get();
    }
}
