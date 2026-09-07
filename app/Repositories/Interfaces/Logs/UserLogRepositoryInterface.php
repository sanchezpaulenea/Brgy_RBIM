<?php

namespace App\Repositories\Interfaces\Logs;

use App\Models\Logs\UserLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface UserLogRepositoryInterface
{
    public function createLog(
        ?int $userId,
        int $loginStatusId,
        string $ipAddress,
        string $device
    ): UserLog;

    public function updateLogoutTime(int $userLogId, Carbon $logoutTime): void;

    /**
     * Close successful login rows that are still open (logout_time equals login_time)
     * and were created before the given user_log_id.
     *
     * @return Collection<int, UserLog>
     */
    public function closeOpenSuccessLogsBefore(int $userId, int $currentUserLogId, Carbon $logoutTime): Collection;

    public function countRecentFailedAttempts(int $userId, int $withinMinutes): int;

    public function getLastLockTime(int $userId): ?Carbon;

    /**
     * @return Collection<int, UserLog>
     */
    public function all(): Collection;
}
