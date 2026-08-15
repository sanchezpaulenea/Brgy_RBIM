<?php

namespace App\Repositories\UserManagement;

use App\Models\UserManagement\Permission;
use App\Repositories\Interfaces\UserManagement\PermissionInterface;
use Illuminate\Database\Eloquent\Collection;

class PermissionRepository implements PermissionInterface
{
    /**
     * @return Collection<int, Permission>
     */
    public function all(): Collection
    {
        return Permission::query()->orderBy('permission')->get();
    }
}
