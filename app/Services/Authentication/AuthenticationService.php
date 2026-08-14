<?php

namespace App\Services\Authentication;

use App\Models\Authentication\LoginStatus;
use App\Models\Authentication\UserLog;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Authentication\UserLogRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use App\Services\SystemSetting\SystemSettingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected UserLogRepositoryInterface $userLogRepository,
        protected SystemSettingService $settingService,
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
        $user = $this->userRepository->findByUsername($username);

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
        $this->touchSessionActivity($request);

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
                now()->setTimezone(config('app.timezone'))
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Change the authenticated user's password.
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

        $minLength = (int) $this->settingService->get('password_min_length');

        if (strlen($newPassword) < $minLength) {
            throw ValidationException::withMessages([
                'new_password' => ["Password must be at least {$minLength} characters."],
            ]);
        }

        $user->password_hash = Hash::make($newPassword);
        $user->must_change_password = false;
        $user->save();
    }

    /**
     * Invalidate the session when idle time exceeds session_timeout_minutes.
     */
    public function enforceSessionTimeout(User $user, Request $request): bool
    {
        $timeoutMinutes = (int) $this->settingService->get('session_timeout_minutes');

        if ($timeoutMinutes <= 0) {
            $this->touchSessionActivity($request);

            return true;
        }

        $lastActivity = $request->session()->get('last_activity');

        if ($lastActivity !== null && $this->isSessionExpired($lastActivity, $timeoutMinutes)) {
            $this->logout($user, $request);

            return false;
        }

        $this->touchSessionActivity($request);

        return true;
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
            'login_time' => $this->formatTimestamp($log->login_time),
            'logout_time' => $this->formatTimestamp($log->logout_time),
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
        $maxAttempts = (int) $this->settingService->get('max_login_attempts');
        $lockoutMinutes = (int) $this->settingService->get('account_lockout_minutes');
        $recentFailed = $this->userLogRepository->countRecentFailedAttempts($user->user_id, $lockoutMinutes);

        if ($maxAttempts > 0 && $recentFailed + 1 >= $maxAttempts) {
            $this->userRepository->lockUserAccount($user->user_id);
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

        $lockoutMinutes = (int) $this->settingService->get('account_lockout_minutes');
        $lastLockTime = $this->userLogRepository->getLastLockTime($user->user_id);

        if ($lastLockTime !== null && $lastLockTime->lte(now()->subMinutes($lockoutMinutes))) {
            $this->userRepository->unlockUserAccount($user->user_id);
            $user->refresh()->load(['userStatus', 'roles.permissions']);
        }
    }

    private function touchSessionActivity(Request $request): void
    {
        $request->session()->put('last_activity', now(config('app.timezone'))->toDateTimeString());
    }

    private function isSessionExpired(mixed $lastActivity, int $timeoutMinutes): bool
    {
        $activityTime = $lastActivity instanceof Carbon
            ? $lastActivity->setTimezone(config('app.timezone'))
            : Carbon::parse((string) $lastActivity, config('app.timezone'));

        return $activityTime->lte(now(config('app.timezone'))->subMinutes($timeoutMinutes));
    }

    private function formatTimestamp(?Carbon $timestamp): ?string
    {
        return $timestamp?->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }

    private function resolveDevice(Request $request): string
    {
        return mb_substr($request->userAgent() ?? 'Unknown', 0, 45);
    }
}
