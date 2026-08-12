<?php

namespace App\Repositories\Authentication;

use App\Models\Authentication\LoginStatus;
use App\Models\Authentication\UserLog;
use App\Repositories\Interfaces\Authentication\UserLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserLogRepository implements UserLogRepositoryInterface
{
    public function createLog(
        int $userId,
        int $loginStatusId,
        string $ipAddress,
        string $device
    ): UserLog {
        $now = now()->toDateTimeString();

        return UserLog::create([
            'user_id' => $userId,
            'login_time' => $now,
            'logout_time' => $now,
            'login_status_id' => $loginStatusId,
            'ip_address' => $ipAddress,
            'device' => mb_substr($device, 0, 45),
        ]);
    }

    public function updateLogoutTime(int $userLogId, string $logoutTime): void
    {
        UserLog::where('user_log_id', $userLogId)->update(['logout_time' => $logoutTime]);
    }

    public function countRecentFailedAttempts(int $userId, int $withinMinutes): int
    {
        return UserLog::query()
            ->where('user_id', $userId)
            ->where('login_status_id', LoginStatus::INVALID_PASSWORD)
            ->where('login_time', '>=', now()->subMinutes($withinMinutes))
            ->count();
    }

    public function getLastLockTime(int $userId): ?string
    {
        $log = UserLog::query()
            ->where('user_id', $userId)
            ->where('login_status_id', LoginStatus::ACCOUNT_LOCKED)
            ->latest('login_time')
            ->first();

        return $log?->login_time?->toDateTimeString();
    }

    /**
     * @return Collection<int, UserLog>
     */
    public function all(): Collection
    {
        return UserLog::query()
            ->with(['user', 'loginStatus'])
            ->orderByDesc('login_time')
            ->get();
    }
}
