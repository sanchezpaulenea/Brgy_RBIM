<?php

namespace Tests\Unit\Authentication;

use App\Models\Authentication\LoginStatus;
use App\Models\Logs\UserLog;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserStatus;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Logs\UserLogRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use App\Services\Authentication\AuthenticationService;
use App\Services\SystemSetting\SystemSettingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class AccountLockoutTest extends TestCase
{
    private const PASSWORD = 'correct-horse';

    private const LOCKOUT_MINUTES = 10;

    private const USER_ID = 4;

    /** @var UserRepositoryInterface&MockInterface */
    private $userRepository;

    /** @var UserLogRepositoryInterface&MockInterface */
    private $userLogRepository;

    /** @var SystemSettingService&MockInterface */
    private $settingService;

    /** @var AuditLogRepositoryInterface&MockInterface */
    private $auditLogRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->userLogRepository = Mockery::mock(UserLogRepositoryInterface::class);
        $this->settingService = Mockery::mock(SystemSettingService::class);
        $this->auditLogRepository = Mockery::mock(AuditLogRepositoryInterface::class);
    }

    public function test_lockout_set_by_an_administrator_reports_the_seconds_left(): void
    {
        $user = $this->makeUser(UserStatus::LOCKED);

        $this->userRepository->shouldReceive('findByUsername')->once()->andReturn($user);
        $this->userRepository->shouldNotReceive('unlockUserAccount');
        $this->stubLockTimes(administrative: now()->subMinutes(4));
        $this->expectLoginLog(LoginStatus::INVALID_CREDENTIALS);
        $this->stubSettings();

        $errors = $this->attemptLogin($user->username, self::PASSWORD);

        $this->assertSame('360', $errors['lockout_remaining_seconds'][0]);
        $this->assertSame('Your account is locked. Please wait before trying again.', $errors['username'][0]);
    }

    public function test_automatic_lockout_reports_the_seconds_left(): void
    {
        $user = $this->makeUser(UserStatus::LOCKED);

        $this->userRepository->shouldReceive('findByUsername')->once()->andReturn($user);
        $this->userRepository->shouldNotReceive('unlockUserAccount');
        $this->stubLockTimes(automatic: now()->subMinutes(2));
        $this->expectLoginLog(LoginStatus::INVALID_CREDENTIALS);
        $this->stubSettings();

        $errors = $this->attemptLogin($user->username, self::PASSWORD);

        $this->assertSame('480', $errors['lockout_remaining_seconds'][0]);
    }

    public function test_the_most_recent_of_the_two_lock_records_wins(): void
    {
        $user = $this->makeUser(UserStatus::LOCKED);

        $this->userRepository->shouldReceive('findByUsername')->once()->andReturn($user);
        $this->stubLockTimes(
            automatic: now()->subMinutes(9),
            administrative: now()->subMinutes(1),
        );
        $this->expectLoginLog(LoginStatus::INVALID_CREDENTIALS);
        $this->stubSettings();

        $errors = $this->attemptLogin($user->username, self::PASSWORD);

        $this->assertSame('540', $errors['lockout_remaining_seconds'][0]);
    }

    public function test_lockout_set_by_an_administrator_expires_on_its_own(): void
    {
        $lockedUser = $this->makeUser(UserStatus::LOCKED);
        $unlockedUser = $this->makeUser(UserStatus::ACTIVE);

        $this->userRepository->shouldReceive('findByUsername')
            ->twice()
            ->andReturn($lockedUser, $unlockedUser);

        $this->userRepository->shouldReceive('unlockUserAccount')
            ->once()
            ->with(self::USER_ID);

        $this->stubLockTimes(administrative: now()->subMinutes(11));
        $this->userLogRepository->shouldReceive('countRecentFailedAttempts')->once()->andReturn(0);
        $this->expectLoginLog(LoginStatus::INVALID_PASSWORD);
        $this->stubSettings(maxAttempts: 3);

        $errors = $this->attemptLogin($lockedUser->username, 'wrong-password');

        $this->assertArrayNotHasKey('lockout_remaining_seconds', $errors);
        $this->assertSame('Invalid credentials.', $errors['username'][0]);
    }

    public function test_lockout_without_a_recorded_time_stays_locked_and_has_no_countdown(): void
    {
        $user = $this->makeUser(UserStatus::LOCKED);

        $this->userRepository->shouldReceive('findByUsername')->once()->andReturn($user);
        $this->userRepository->shouldNotReceive('unlockUserAccount');
        $this->stubLockTimes();
        $this->expectLoginLog(LoginStatus::INVALID_CREDENTIALS);
        $this->stubSettings();

        $errors = $this->attemptLogin($user->username, self::PASSWORD);

        $this->assertArrayNotHasKey('lockout_remaining_seconds', $errors);
        $this->assertSame(
            'Your account is locked or disabled. Please contact an administrator.',
            $errors['username'][0]
        );
    }

    public function test_the_attempt_that_locks_the_account_returns_the_countdown_immediately(): void
    {
        $user = $this->makeUser(UserStatus::ACTIVE);

        $this->userRepository->shouldReceive('findByUsername')->once()->andReturn($user);
        $this->userRepository->shouldReceive('lockUserAccount')->once()->with(self::USER_ID);
        $this->userLogRepository->shouldReceive('countRecentFailedAttempts')->once()->andReturn(0);
        $this->stubLockTimes(automatic: now());
        $this->expectLoginLog(LoginStatus::ACCOUNT_LOCKED);
        $this->stubSettings(maxAttempts: 1);

        $errors = $this->attemptLogin($user->username, 'wrong-password');

        $this->assertSame('600', $errors['lockout_remaining_seconds'][0]);
        $this->assertSame('Too many failed attempts. Your account has been locked.', $errors['username'][0]);
    }

    public function test_a_failed_attempt_below_the_threshold_does_not_lock_the_account(): void
    {
        $user = $this->makeUser(UserStatus::ACTIVE);

        $this->userRepository->shouldReceive('findByUsername')->once()->andReturn($user);
        $this->userRepository->shouldNotReceive('lockUserAccount');
        $this->userLogRepository->shouldReceive('countRecentFailedAttempts')->once()->andReturn(0);
        $this->expectLoginLog(LoginStatus::INVALID_PASSWORD);
        $this->stubSettings(maxAttempts: 3);

        $errors = $this->attemptLogin($user->username, 'wrong-password');

        $this->assertArrayNotHasKey('lockout_remaining_seconds', $errors);
        $this->assertSame('Invalid credentials.', $errors['username'][0]);
    }

    public function test_disabled_account_is_not_given_a_countdown(): void
    {
        $user = $this->makeUser(UserStatus::DISABLED);

        $this->userRepository->shouldReceive('findByUsername')->once()->andReturn($user);
        $this->userRepository->shouldNotReceive('unlockUserAccount');
        $this->userLogRepository->shouldNotReceive('getLastLockTime');
        $this->auditLogRepository->shouldNotReceive('getLastUserStatusChangeTime');
        $this->expectLoginLog(LoginStatus::INVALID_CREDENTIALS);
        $this->stubSettings();

        $errors = $this->attemptLogin($user->username, self::PASSWORD);

        $this->assertArrayNotHasKey('lockout_remaining_seconds', $errors);
        $this->assertSame(
            'Your account is locked or disabled. Please contact an administrator.',
            $errors['username'][0]
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function attemptLogin(string $username, string $password): array
    {
        $service = new AuthenticationService(
            $this->userRepository,
            $this->userLogRepository,
            $this->settingService,
            $this->auditLogRepository,
        );

        $request = Request::create('/api/v1/auth/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'PHPUnit',
        ]);

        try {
            $service->login($username, $password, $request);
        } catch (ValidationException $exception) {
            return $exception->errors();
        }

        $this->fail('Expected the login attempt to be rejected.');
    }

    private function makeUser(int $userStatusId): User
    {
        $user = new User([
            'username' => 'victor',
            'password_hash' => Hash::make(self::PASSWORD),
            'user_status_id' => $userStatusId,
        ]);

        $user->user_id = self::USER_ID;

        $user->setRelation('userStatus', new UserStatus([
            'user_status' => match ($userStatusId) {
                UserStatus::ACTIVE => 'Active',
                UserStatus::DISABLED => 'Disabled',
                UserStatus::SUSPENDED => 'Suspended',
                default => 'Locked',
            },
            'can_login' => $userStatusId === UserStatus::ACTIVE,
        ]));

        return $user;
    }

    private function stubLockTimes(?Carbon $automatic = null, ?Carbon $administrative = null): void
    {
        $this->userLogRepository
            ->shouldReceive('getLastLockTime')
            ->with(self::USER_ID)
            ->andReturn($automatic);

        $this->auditLogRepository
            ->shouldReceive('getLastUserStatusChangeTime')
            ->with(self::USER_ID, UserStatus::LOCKED)
            ->andReturn($administrative);
    }

    private function expectLoginLog(int $loginStatusId): void
    {
        $this->userLogRepository
            ->shouldReceive('createLog')
            ->once()
            ->with(self::USER_ID, $loginStatusId, '127.0.0.1', Mockery::type('string'))
            ->andReturn(new UserLog);
    }

    private function stubSettings(int $maxAttempts = 5): void
    {
        $settings = [
            'account_lockout_minutes' => self::LOCKOUT_MINUTES,
            'max_login_attempts' => $maxAttempts,
        ];

        $this->settingService
            ->shouldReceive('get')
            ->andReturnUsing(fn (string $key) => $settings[$key] ?? '');
    }
}
