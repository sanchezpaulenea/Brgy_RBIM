<?php

namespace App\Repositories\Interfaces\UserManagement;

use App\Models\UserManagement\Role;
use Illuminate\Database\Eloquent\Collection;

interface RoleInterface
{
    /**
     * @return Collection<int, Role>
     */
    public function all(): Collection;

    /**
     * @return Collection<int, Role>
     */
    public function allWithPermissions(): Collection;

    public function findById(int $roleId): ?Role;
}
