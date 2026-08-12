<?php

namespace App\Services\Authentication;

use App\Models\Authentication\LoginStatus;
use App\Models\Authentication\UserLog;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Authentication\SessionRepositoryInterface;
use App\Repositories\Interfaces\Authentication\UserLogRepositoryInterface;
use App\Repositories\Interfaces\SystemSetting\SystemSettingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SessionService
{
    public function __construct(
        protected SessionRepositoryInterface $sessionRepository,
        protected UserLogRepositoryInterface $userLogRepository,
        protected SystemSettingRepositoryInterface $systemSettingRepository,
    ) {}

    /**
     * Authenticate a user and start a stateful Sanctum session.
     *
     * Login status mapping:
     *   - username not found → Invalid Username (3) — cannot be written to user_log
     *     because user_log.user_id is NOT NULL (no FK target exists).
     *   - wrong password → Invalid Password (2), or Account Locked (5) at threshold
     *   - user_status.can_login = false → Invalid Credentials (4)
     *   - success → Success (1)
     *
     * @return array{user: User, must_change_password: bool, roles: Collection, permissions: Collection}
     *
     * @throws ValidationException
     */
    public function login(string $username, string $password, Request $request): array
    {
        $user = $this->sessionRepository->findByUsername($username);

        if (! $user) {
            throw ValidationException::withMessages([
                'username' => ['Invalid credentials.'],
            ]);
        }

        $this->attemptAutoUnlock($user);

        if (! $user->userStatus->can_login) {
            $this->userLogRepository->createLog(
                $user->user_id,
                LoginStatus::INVALID_CREDENTIALS,
                $request->ip(),
                $this->resolveDevice($request)
            );

            throw ValidationException::withMessages([
                'username' => ['Your account is locked or disabled. Please contact an administrator.'],
            ]);
        }

        if (! Hash::check($password, $user->password_hash)) {
            $this->handleFailedPasswordAttempt($user, $request);

            throw ValidationException::withMessages([
                'username' => ['Invalid credentials.'],
            ]);
        }

        $log = $this->userLogRepository->createLog(
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
     * End the current session and record logout_time on the open user_log row.
     */
    public function logout(User $user, Request $request): void
    {
        $userLogId = $request->session()->get('user_log_id');

        if ($userLogId) {
            $this->userLogRepository->updateLogoutTime(
                (int) $userLogId,
                now()->toDateTimeString()
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listLoginHistory(): array
    {
        return $this->userLogRepository
            ->all()
            ->map(fn (UserLog $log) => $this->formatUserLog($log))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatUserLog(UserLog $log): array
    {
        $log->loadMissing(['user', 'loginStatus']);

        return [
            'user_log_id' => $log->user_log_id,
            'user_id' => $log->user_id,
            'username' => $log->user?->username,
            'login_time' => $log->login_time,
            'logout_time' => $log->logout_time,
            'login_status_id' => $log->login_status_id,
            'login_status' => $log->loginStatus?->login_status,
            'ip_address' => $log->ip_address,
            'device' => $log->device,
        ];
    }

    /**
     * Count recent Invalid Password attempts within the lockout window.
     * When the threshold is reached, lock the account and log Account Locked (5).
     */
    private function handleFailedPasswordAttempt(User $user, Request $request): void
    {
        $maxAttempts = (int) $this->systemSettingRepository->getValue('max_login_attempts');
        $lockoutMinutes = (int) $this->systemSettingRepository->getValue('account_lockout_minutes');
        $recentFailed = $this->userLogRepository->countRecentFailedAttempts($user->user_id, $lockoutMinutes);

        if ($recentFailed + 1 >= $maxAttempts) {
            $this->sessionRepository->lockUserAccount($user->user_id);
            $statusId = LoginStatus::ACCOUNT_LOCKED;
        } else {
            $statusId = LoginStatus::INVALID_PASSWORD;
        }

        $this->userLogRepository->createLog(
            $user->user_id,
            $statusId,
            $request->ip(),
            $this->resolveDevice($request)
        );
    }

    private function attemptAutoUnlock(User $user): void
    {
        if ($user->userStatus->user_status !== 'Locked') {
            return;
        }

        $lockoutMinutes = (int) $this->systemSettingRepository->getValue('account_lockout_minutes');
        $lastLockTime = $this->userLogRepository->getLastLockTime($user->user_id);

        if ($lastLockTime && now()->diffInMinutes($lastLockTime) >= $lockoutMinutes) {
            $this->sessionRepository->unlockUserAccount($user->user_id);
            $user->refresh()->load(['userStatus', 'roles.permissions']);
        }
    }

    private function resolveDevice(Request $request): string
    {
        return mb_substr($request->userAgent() ?? 'Unknown', 0, 45);
    }
}
