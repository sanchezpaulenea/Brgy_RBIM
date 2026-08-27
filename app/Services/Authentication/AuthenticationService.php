<?php

namespace App\Services\Authentication;

use App\Models\Authentication\LoginStatus;
use App\Models\BarangayPersonnel\PersonnelStatus;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserStatus;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Logs\UserLogRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use App\Services\SystemSetting\SystemSettingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthenticationService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected UserLogRepositoryInterface $userLogRepository,
        protected SystemSettingService $settingService,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * Authenticate a user and start a stateful Sanctum session.
     *
     * Login status mapping:
     *   - username not found → Invalid Username (3), stored with a null user_id
     *   - wrong password → Invalid Password (2), or Account Locked (5) at threshold
     *   - user_status.can_login = false → Invalid Credentials (4)
     *   - linked personnel_status is not Active → Invalid Credentials (4)
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
            $this->userLogRepository->createLog(
                null,
                LoginStatus::INVALID_USERNAME,
                $request->ip(),
                $this->resolveDevice($request)
            );

            throw ValidationException::withMessages([
                'username' => ['Invalid username.'],
            ]);
        }

        if ($this->attemptAutoUnlock($user)) {
            $user = $this->userRepository->findByUsername($username) ?? $user;
        }

        if (! $user->userStatus->can_login) {
            $this->userLogRepository->createLog(
                $user->user_id,
                LoginStatus::INVALID_CREDENTIALS,
                $request->ip(),
                $this->resolveDevice($request)
            );

            $remainingSeconds = $this->isLocked($user)
                ? $this->remainingLockoutSeconds($user)
                : 0;

            if ($remainingSeconds > 0) {
                throw ValidationException::withMessages([
                    'username' => ['Your account is locked. Please wait before trying again.'],
                    'lockout_remaining_seconds' => [(string) $remainingSeconds],
                ]);
            }

            throw ValidationException::withMessages([
                'username' => ['Your account is locked or disabled. Please contact an administrator.'],
            ]);
        }

        if ($this->personnelAccessIsDenied($user)) {
            $this->userLogRepository->createLog(
                $user->user_id,
                LoginStatus::INVALID_CREDENTIALS,
                $request->ip(),
                $this->resolveDevice($request)
            );

            throw ValidationException::withMessages([
                'username' => ['Your personnel record is not active. Please contact an administrator.'],
            ]);
        }

        if (! Hash::check($password, $user->password_hash)) {
            if ($this->handleFailedPasswordAttempt($user, $request)) {
                throw ValidationException::withMessages([
                    'username' => ['Too many failed attempts. Your account has been locked.'],
                    'lockout_remaining_seconds' => [(string) $this->remainingLockoutSeconds($user)],
                ]);
            }

            throw ValidationException::withMessages([
                'password' => ['Invalid password, please try again.'],
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

        Auth::guard('web')->logout();
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

        DB::transaction(function () use ($user, $newPassword) {
            $user->password_hash = Hash::make($newPassword);
            $user->must_change_password = false;
            $user->save();

            $this->auditLogRepository->log(
                performedByUserId: $user->user_id,
                actionId: Action::UPDATE,
                recordId: $user->user_id,
                description: 'User changed password',
                oldValue: '[REDACTED]',
                newValue: '[REDACTED]',
                target: 'password',
                entity: 'user',
            );
        });
    }

    /**
     * Re-read user_status and linked personnel_status from the database.
     * When either no longer allows access, the session is ended immediately.
     */
    public function enforceAccountAccess(User $user, Request $request): ?string
    {
        $fresh = $this->userRepository->findWithAccessState($user->user_id);

        if ($fresh === null || ! $fresh->userStatus?->can_login) {
            $this->logout($user, $request);

            return 'Your account is locked or disabled. Please contact an administrator.';
        }

        if ($this->personnelAccessIsDenied($fresh)) {
            $this->logout($user, $request);

            return 'Your personnel record is not active. Please contact an administrator.';
        }

        return null;
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
     * Count recent Invalid Password attempts within the lockout window.
     * When the threshold is reached, lock the account and log Account Locked (5).
     *
     * @return bool Whether this attempt locked the account.
     */
    private function handleFailedPasswordAttempt(User $user, Request $request): bool
    {
        $maxAttempts = (int) $this->settingService->get('max_login_attempts');
        $lockoutMinutes = (int) $this->settingService->get('account_lockout_minutes');
        $recentFailed = $this->userLogRepository->countRecentFailedAttempts($user->user_id, $lockoutMinutes);

        $locksAccount = $maxAttempts > 0 && $recentFailed + 1 >= $maxAttempts;

        if ($locksAccount) {
            $this->userRepository->lockUserAccount($user->user_id);
        }

        $this->userLogRepository->createLog(
            $user->user_id,
            $locksAccount ? LoginStatus::ACCOUNT_LOCKED : LoginStatus::INVALID_PASSWORD,
            $request->ip(),
            $this->resolveDevice($request)
        );

        return $locksAccount;
    }

    /**
     * Release a lockout once its window has elapsed. A locked account with no
     * recorded lock time has no window to expire and stays locked until an
     * administrator reactivates it.
     *
     * @return bool Whether the account was unlocked.
     */
    private function attemptAutoUnlock(User $user): bool
    {
        if (! $this->isLocked($user)) {
            return false;
        }

        $lastLockTime = $this->lastLockTime($user);

        if ($lastLockTime === null || $this->remainingSecondsFrom($lastLockTime) > 0) {
            return false;
        }

        $this->userRepository->unlockUserAccount($user->user_id);

        return true;
    }

    private function isLocked(User $user): bool
    {
        return (int) $user->user_status_id === UserStatus::LOCKED;
    }

    /**
     * The most recent moment the account entered the Locked status: repeated failed
     * logins leave an Account Locked entry in `user_log`, while an administrator
     * setting the status leaves an entry in `audit_log`.
     */
    private function lastLockTime(User $user): ?Carbon
    {
        $automatic = $this->userLogRepository->getLastLockTime($user->user_id);
        $administrative = $this->auditLogRepository->getLastUserStatusChangeTime(
            $user->user_id,
            UserStatus::LOCKED
        );

        if ($automatic === null || $administrative === null) {
            return $automatic ?? $administrative;
        }

        return $automatic->greaterThan($administrative) ? $automatic : $administrative;
    }

    private function remainingLockoutSeconds(User $user): int
    {
        $lastLockTime = $this->lastLockTime($user);

        if ($lastLockTime === null) {
            return 0;
        }

        return $this->remainingSecondsFrom($lastLockTime);
    }

    /**
     * Seconds left in the lockout window that started at the given moment.
     */
    private function remainingSecondsFrom(Carbon $lockedAt): int
    {
        $lockoutMinutes = max(0, (int) $this->settingService->get('account_lockout_minutes'));
        $unlocksAt = $lockedAt->copy()->addMinutes($lockoutMinutes);

        return (int) max(0, $unlocksAt->getTimestamp() - now()->getTimestamp());
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

    private function resolveDevice(Request $request): string
    {
        return mb_substr($request->userAgent() ?? 'Unknown', 0, 45);
    }

    private function personnelAccessIsDenied(User $user): bool
    {
        if ($user->personnel_id === null) {
            return false;
        }

        $personnel = $user->relationLoaded('personnel')
            ? $user->personnel
            : $user->personnel()->first();

        return $personnel === null
            || (int) $personnel->personnel_status_id !== PersonnelStatus::ACTIVE;
    }

    /**
     * @return array<string, mixed>
     */
    public function sessionUser(User $user): array
    {
        $user->loadMissing(['personnel.position']);

        return [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'full_name' => $user->profileDisplayName(),
            'first_name' => $user->profileGivenName() ?: null,
            'last_name' => $user->profileFamilyName() ?: null,
            'position_name' => $user->profilePositionName(),
            'avatar_preset' => $user->avatar_preset,
            'avatar_url' => $user->avatarUrl(),
            'must_change_password' => (bool) $user->must_change_password,
        ];
    }

    /**
     * @return array{user: array<string, mixed>, roles: Collection<int, mixed>, permissions: Collection<int, mixed>}
     */
    public function sessionPayload(User $user, bool $includeStatus = false): array
    {
        $user->loadMissing(['userStatus', 'roles.permissions', 'personnel.position']);

        $payload = $this->sessionUser($user);

        if ($includeStatus) {
            $payload['user_status'] = $user->userStatus?->user_status;
            $payload['created_at'] = $user->created_at;
        }

        return [
            'user' => $payload,
            'roles' => $user->roles->pluck('role_name'),
            'permissions' => $user->permissions()->pluck('permission'),
        ];
    }

    public function updateAvatar(User $user, ?UploadedFile $photo, ?string $preset): User
    {
        if ($photo !== null) {
            $this->storeProfilePhoto($user, $photo);

            return $user->fresh(['personnel.position', 'userStatus', 'roles.permissions']) ?? $user;
        }

        $this->deleteProfilePhoto($user);
        $user->avatar_preset = $preset;
        $user->save();

        return $user->fresh(['personnel.position', 'userStatus', 'roles.permissions']) ?? $user;
    }

    public function avatarResponse(User $user): StreamedResponse
    {
        if (! is_string($user->avatar_path) || $user->avatar_path === '') {
            throw new NotFoundHttpException;
        }

        if (! Storage::disk('local')->exists($user->avatar_path)) {
            throw new NotFoundHttpException;
        }

        return Storage::disk('local')->response($user->avatar_path);
    }

    private function storeProfilePhoto(User $user, UploadedFile $photo): void
    {
        $this->deleteProfilePhoto($user);

        $extension = $photo->guessExtension() ?: $photo->getClientOriginalExtension() ?: 'jpg';
        $path = $photo->storeAs('avatars', $user->user_id.'.'.$extension, 'local');

        $user->avatar_path = $path;
        $user->save();
    }

    private function deleteProfilePhoto(User $user): void
    {
        if (is_string($user->avatar_path) && $user->avatar_path !== '') {
            Storage::disk('local')->delete($user->avatar_path);
        }

        $user->avatar_path = null;
    }
}
