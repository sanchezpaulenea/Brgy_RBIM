<?php

namespace App\Repositories\Interfaces\Authentication;

use App\Models\UserManagement\User;

interface SessionRepositoryInterface
{
    public function findByUsername(string $username): ?User;

    public function lockUserAccount(int $userId): void;

    public function unlockUserAccount(int $userId): void;
}
