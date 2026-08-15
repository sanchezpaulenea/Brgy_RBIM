<?php

namespace App\Repositories\Interfaces\UserManagement;

use App\Models\UserManagement\RolePermission;
use Illuminate\Database\Eloquent\Collection;

interface RolePermissionInterface
{
    /**
     * @return Collection<int, RolePermission>
     */
    public function all(): Collection;
}
