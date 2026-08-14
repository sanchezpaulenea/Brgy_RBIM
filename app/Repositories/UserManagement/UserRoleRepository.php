<?php

namespace App\Repositories\UserManagement;

use App\Models\UserManagement\UserRole;
use App\Repositories\Interfaces\UserManagement\UserRoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRoleRepository implements UserRoleRepositoryInterface
{
    /**
     * @return Collection<int, UserRole>
     */
    public function getByUserId(int $userId): Collection
    {
        return UserRole::query()
            ->with(['role', 'assignedBy'])
            ->where('user_id', $userId)
            ->orderByDesc('assigned_at')
            ->get();
    }

    public function findById(int $userRoleId): ?UserRole
    {
        return UserRole::query()
            ->with(['role', 'user', 'assignedBy'])
            ->where('user_role_id', $userRoleId)
            ->first();
    }

    public function create(array $attributes): UserRole
    {
        return UserRole::create($attributes);
    }

    public function updateEnableStatus(UserRole $userRole, bool $enable): UserRole
    {
        $userRole->enable = $enable;
        $userRole->save();

        return $userRole->fresh(['role', 'assignedBy']);
    }

    public function existsForUserAndRole(int $userId, int $roleId): bool
    {
        return UserRole::query()
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();
    }
}
