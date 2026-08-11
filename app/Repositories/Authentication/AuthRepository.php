<?php

namespace App\Repositories\Authentication;

use App\Models\Authentication\LoginStatus;
use App\Models\Authentication\UserLog;
use App\Models\SystemSetting\SystemSetting;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Authentication\AuthRepositoryInterface;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByUsername(string $username): ?User
    {
        return User::with(['userStatus', 'roles.permissions'])
            ->where('username', $username)
            ->first();
    }

    /**
     * {@inheritdoc}
     *
     * `logout_time` is NOT NULL in the schema, so we default it to `login_time`.
     * It will be updated to the real time when the user logs out.
     */
    public function createLoginLog(
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

    /**
     * {@inheritdoc}
     */
    public function updateLogoutTime(int $userLogId, string $logoutTime): void
    {
        UserLog::where('user_log_id', $userLogId)->update(['logout_time' => $logoutTime]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemSetting(string $key): string
    {
        $setting = SystemSetting::where('setting_key', $key)->first();

        return $setting?->setting_value ?? '';
    }

    /**
     * {@inheritdoc}
     *
     * Counts `user_log` rows with invalid-password or generic-invalid-credentials
     * statuses within the rolling lockout window.
     */
    public function countRecentFailedAttempts(int $userId, int $withinMinutes): int
    {
        return UserLog::where('user_id', $userId)
            ->whereIn('login_status_id', [
                LoginStatus::INVALID_PASSWORD,
                LoginStatus::INVALID_CREDENTIALS,
            ])
            ->where('login_time', '>=', now()->subMinutes($withinMinutes))
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function lockUserAccount(int $userId): void
    {
        /** @var int $lockedStatusId */
        User::where('user_id', $userId)->update(['user_status_id' => 3]);
    }

    /**
     * {@inheritdoc}
     */
    public function unlockUserAccount(int $userId): void
    {
        /** @var int $activeStatusId */
        User::where('user_id', $userId)->update(['user_status_id' => 1]);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastLockTime(int $userId): ?string
    {
        $log = UserLog::where('user_id', $userId)
            ->where('login_status_id', LoginStatus::ACCOUNT_LOCKED)
            ->latest('login_time')
            ->first();

        return $log?->login_time?->toDateTimeString();
    }
}
