<?php

namespace App\Repositories\Interfaces\Authentication;

use App\Models\Authentication\UserLog;
use Illuminate\Database\Eloquent\Collection;

interface UserLogRepositoryInterface
{
    public function createLog(
        int $userId,
        int $loginStatusId,
        string $ipAddress,
        string $device
    ): UserLog;

    public function updateLogoutTime(int $userLogId, string $logoutTime): void;

    public function countRecentFailedAttempts(int $userId, int $withinMinutes): int;

    public function getLastLockTime(int $userId): ?string;

    /**
     * @return Collection<int, UserLog>
     */
    public function all(): Collection;
}
