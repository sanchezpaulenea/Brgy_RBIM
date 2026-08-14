<?php

namespace App\Repositories\Interfaces\UserManagement;

use App\Models\UserManagement\UserRole;
use Illuminate\Database\Eloquent\Collection;

interface UserRoleRepositoryInterface
{
    /**
     * @return Collection<int, UserRole>
     */
    public function getByUserId(int $userId): Collection;

    public function findById(int $userRoleId): ?UserRole;

    public function create(array $attributes): UserRole;

    public function updateEnableStatus(UserRole $userRole, bool $enable): UserRole;

    public function existsForUserAndRole(int $userId, int $roleId): bool;
}
