<?php

namespace App\Services\Authentication;

use App\Models\Authentication\LoginStatus;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Authentication\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected AuthRepositoryInterface $authRepository
    ) {}

    /**
     * Authenticate a user by username and password.
     *
     * Flow:
     *   1. Find user by username. If not found, return error (no log — no valid user_id for FK).
     *   2. If user is Locked, check if lockout window has expired and auto-unlock if so.
     *   3. Check user_status.can_login; reject if false (Disabled, Suspended, still Locked).
     *   4. Verify password hash.
     *   5. On wrong password, count recent failures and lock the account if threshold is reached.
     *   6. On success, start session, store user_log_id, and return user profile.
     *
     * @return array{user: User, must_change_password: bool, roles: Collection, permissions: Collection}
     *
     * @throws ValidationException
     */
    public function login(string $username, string $password, Request $request): array
    {
        // 1. Find user — no log possible without a valid user_id FK.
        $user = $this->authRepository->findByUsername($username);

        if (! $user) {
            throw ValidationException::withMessages([
                'username' => ['Invalid credentials.'],
            ]);
        }

        // 2. If account is Locked, check if the lockout window has expired.
        if ($user->userStatus->user_status === 'Locked') {
            $lockoutMinutes = (int) $this->authRepository->getSystemSetting('account_lockout_minutes');
            $lastLockTime = $this->authRepository->getLastLockTime($user->user_id);

            if ($lastLockTime && now()->diffInMinutes($lastLockTime) >= $lockoutMinutes) {
                $this->authRepository->unlockUserAccount($user->user_id);
                $user->refresh()->load(['userStatus', 'roles.permissions']);
            }
        }

        // 3. Check whether this status permits login.
        if (! $user->userStatus->can_login) {
            $this->authRepository->createLoginLog(
                $user->user_id,
                LoginStatus::ACCOUNT_LOCKED,
                $request->ip(),
                $this->resolveDevice($request)
            );

            throw ValidationException::withMessages([
                'username' => ['Your account is locked or disabled. Please contact an administrator.'],
            ]);
        }

        // 4. Verify password.
        if (! Hash::check($password, $user->password_hash)) {
            $this->handleFailedPasswordAttempt($user, $request);

            throw ValidationException::withMessages([
                'username' => ['Invalid credentials.'],
            ]);
        }

        // 5. Successful authentication — create the log entry.
        $log = $this->authRepository->createLoginLog(
            $user->user_id,
            LoginStatus::SUCCESS,
            $request->ip(),
            $this->resolveDevice($request)
        );

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('user_log_id', $log->user_log_id);

        return [
            'user' => $user,
            'must_change_password' => (bool) $user->must_change_password,
            'roles' => $user->roles->pluck('role_name'),
            'permissions' => $user->permissions()->pluck('permission'),
        ];
    }

    /**
     * Log the user out, record the logout time, and invalidate the session.
     */
    public function logout(User $user, Request $request): void
    {
        $userLogId = $request->session()->get('user_log_id');

        if ($userLogId) {
            $this->authRepository->updateLogoutTime(
                (int) $userLogId,
                now()->toDateTimeString()
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Change the authenticated user's password.
     *
     * Validates current password, enforces minimum-length from system_setting,
     * hashes the new password, and resets the must_change_password flag.
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password_hash)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $minLength = (int) $this->authRepository->getSystemSetting('password_min_length');

        if (strlen($newPassword) < $minLength) {
            throw ValidationException::withMessages([
                'new_password' => ["Password must be at least {$minLength} characters."],
            ]);
        }

        $user->password_hash = Hash::make($newPassword);
        $user->must_change_password = false;
        $user->save();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Count recent failed password attempts. If the threshold is reached,
     * lock the account and log Account Locked; otherwise log Invalid Password.
     */
    private function handleFailedPasswordAttempt(User $user, Request $request): void
    {
        $maxAttempts = (int) $this->authRepository->getSystemSetting('max_login_attempts');
        $lockoutMinutes = (int) $this->authRepository->getSystemSetting('account_lockout_minutes');
        $recentFailed = $this->authRepository->countRecentFailedAttempts($user->user_id, $lockoutMinutes);

        if ($recentFailed + 1 >= $maxAttempts) {
            $this->authRepository->lockUserAccount($user->user_id);
            $statusId = LoginStatus::ACCOUNT_LOCKED;
        } else {
            $statusId = LoginStatus::INVALID_PASSWORD;
        }

        $this->authRepository->createLoginLog(
            $user->user_id,
            $statusId,
            $request->ip(),
            $this->resolveDevice($request)
        );
    }

    /**
     * Extract a safe device string (max 45 chars) from the User-Agent header.
     */
    private function resolveDevice(Request $request): string
    {
        return mb_substr($request->userAgent() ?? 'Unknown', 0, 45);
    }
}
