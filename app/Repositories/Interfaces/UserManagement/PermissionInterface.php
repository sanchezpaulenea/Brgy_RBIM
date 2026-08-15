<?php

namespace App\Repositories\Interfaces\UserManagement;

use App\Models\UserManagement\Permission;
use Illuminate\Database\Eloquent\Collection;

interface PermissionInterface
{
    /**
     * @return Collection<int, Permission>
     */
    public function all(): Collection;
}
