<?php

namespace App\Repositories\Logs;

use App\Models\Authentication\LoginStatus;
use App\Models\Logs\UserLog;
use App\Repositories\Interfaces\Logs\UserLogRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class UserLogRepository implements UserLogRepositoryInterface
{
    public function createLog(
        ?int $userId,
        int $loginStatusId,
        string $ipAddress,
        string $device
    ): UserLog {
        $timestamp = Carbon::now(config('app.timezone'));

        return UserLog::create([
            'user_id' => $userId,
            'login_time' => $timestamp->copy()->format('Y-m-d H:i:s'),
            'logout_time' => $timestamp->copy()->format('Y-m-d H:i:s'),
            'login_status_id' => $loginStatusId,
            'ip_address' => $ipAddress,
            'device' => mb_substr($device, 0, 45),
        ]);
    }

    public function updateLogoutTime(int $userLogId, Carbon $logoutTime): void
    {
        $value = $logoutTime
            ->copy()
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');

        UserLog::where('user_log_id', $userLogId)->update(['logout_time' => $value]);
    }

    /**
     * @return Collection<int, UserLog>
     */
    public function closeOpenSuccessLogsBefore(int $userId, int $currentUserLogId, Carbon $logoutTime): Collection
    {
        $value = $logoutTime
            ->copy()
            ->setTimezone(config('app.timezone'))
            ->format('Y-m-d H:i:s');

        $openLogs = UserLog::query()
            ->where('user_id', $userId)
            ->where('login_status_id', LoginStatus::SUCCESS)
            ->where('user_log_id', '<', $currentUserLogId)
            ->whereColumn('logout_time', 'login_time')
            ->get();

        if ($openLogs->isEmpty()) {
            return $openLogs;
        }

        UserLog::query()
            ->whereIn('user_log_id', $openLogs->pluck('user_log_id'))
            ->update(['logout_time' => $value]);

        $openLogs->each(function (UserLog $log) use ($logoutTime): void {
            $log->logout_time = $logoutTime->copy()->setTimezone(config('app.timezone'));
        });

        return $openLogs;
    }

    public function countRecentFailedAttempts(int $userId, int $withinMinutes): int
    {
        if ($withinMinutes <= 0) {
            return 0;
        }

        $threshold = Carbon::now(config('app.timezone'))->subMinutes($withinMinutes);

        return UserLog::query()
            ->where('user_id', $userId)
            ->where('login_status_id', LoginStatus::INVALID_PASSWORD)
            ->where('login_time', '>=', $threshold->format('Y-m-d H:i:s'))
            ->count();
    }

    /**
     * When the account was most recently locked, whether by repeated failed
     * logins or by an administrator setting the status to Locked.
     */
    public function getLastLockTime(int $userId): ?Carbon
    {
        $log = UserLog::query()
            ->where('user_id', $userId)
            ->where('login_status_id', LoginStatus::ACCOUNT_LOCKED)
            ->latest('login_time')
            ->first();

        return $log?->login_time?->setTimezone(config('app.timezone'));
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
