<?php

namespace App\Repositories\Authentication;

use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Authentication\SessionRepositoryInterface;

class SessionRepository implements SessionRepositoryInterface
{
    public function findByUsername(string $username): ?User
    {
        return User::with(['userStatus', 'roles.permissions'])
            ->where('username', $username)
            ->first();
    }

    public function lockUserAccount(int $userId): void
    {
        User::where('user_id', $userId)->update(['user_status_id' => 3]);
    }

    public function unlockUserAccount(int $userId): void
    {
        User::where('user_id', $userId)->update(['user_status_id' => 1]);
    }
}
