<?php

namespace App\Repositories\UserManagement;

use App\Models\SystemSetting\SystemSetting;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @return Collection<int, User>
     */
    public function all(): Collection
    {
        return User::query()
            ->with(['userStatus', 'personnel', 'roles'])
            ->orderBy('username')
            ->get();
    }

    public function findById(int $userId): ?User
    {
        return User::query()
            ->with(['userStatus', 'personnel', 'roles', 'userRoles.role', 'userRoles.assignedBy'])
            ->where('user_id', $userId)
            ->first();
    }

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function updateStatus(User $user, int $userStatusId): User
    {
        $user->user_status_id = $userStatusId;
        $user->save();

        return $user->fresh(['userStatus']);
    }

    public function resetPassword(User $user, string $plainPassword): User
    {
        $user->password_hash = Hash::make($plainPassword);
        $user->must_change_password = true;
        $user->save();

        return $user->fresh();
    }

    public function getSystemSetting(string $key): string
    {
        $setting = SystemSetting::where('setting_key', $key)->first();

        return $setting?->setting_value ?? '';
    }

    public function personnelIsLinked(int $personnelId, ?int $exceptUserId = null): bool
    {
        return User::query()
            ->where('personnel_id', $personnelId)
            ->when($exceptUserId !== null, fn ($query) => $query->where('user_id', '!=', $exceptUserId))
            ->exists();
    }
}
