<?php

namespace App\Repositories\Interfaces\Authentication;

use App\Models\Authentication\UserLog;
use App\Models\UserManagement\User;

/**
 * Defines the repository contract for all authentication-related DB operations.
 * The concrete implementation is bound in RepositoryServiceProvider.
 */
interface AuthRepositoryInterface
{
    /**
     * Retrieve a user by their username.
     * Eager-loads `userStatus`, `roles`, and `roles.permissions`.
     */
    public function findByUsername(string $username): ?User;

    /**
     * Create a new entry in `user_log` for a login attempt (success or failure).
     * Since `logout_time` is NOT NULL, it is seeded to the same value as `login_time`
     * and updated to the real logout time via updateLogoutTime().
     *
     * @param  int  $loginStatusId  One of the LoginStatus::* constants
     * @param  string  $device  Truncated User-Agent (max 45 chars)
     */
    public function createLoginLog(
        int $userId,
        int $loginStatusId,
        string $ipAddress,
        string $device
    ): UserLog;

    /**
     * Update the logout_time of an existing user_log record when the user logs out.
     */
    public function updateLogoutTime(int $userLogId, string $logoutTime): void;

    /**
     * Retrieve a system_setting value by its key.
     */
    public function getSystemSetting(string $key): string;

    /**
     * Count failed login attempts (login_status_id IN [2,4]) for a user
     * within the last N minutes, used for lockout threshold checks.
     */
    public function countRecentFailedAttempts(int $userId, int $withinMinutes): int;

    /**
     * Set the user's status to Locked (user_status_id = 3).
     */
    public function lockUserAccount(int $userId): void;

    /**
     * Set the user's status to Active (user_status_id = 1), used when lockout expires.
     */
    public function unlockUserAccount(int $userId): void;

    /**
     * Return the datetime string of the most recent Account Locked log for a user.
     * Returns null if no such log exists.
     */
    public function getLastLockTime(int $userId): ?string;
}
