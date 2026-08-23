<?php

namespace App\Repositories\Authentication;

use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Authentication\SessionRepositoryInterface;

class SessionRepository implements SessionRepositoryInterface
{
    public function findByUsername(string $username): ?User
    {
        $normalized = User::standardizeUsername($username);

        return User::with(['userStatus', 'roles.permissions'])
            ->whereRaw('LOWER(username) = ?', [$normalized])
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
