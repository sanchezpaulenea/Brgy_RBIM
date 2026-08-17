<?php

namespace Tests\Unit\Authentication;

use App\Models\Authentication\LoginStatus;
use App\Models\Logs\UserLog;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Logs\UserLogRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use App\Services\Authentication\AuthenticationService;
use App\Services\SystemSetting\SystemSettingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class UnknownAccountLoginTest extends TestCase
{
    public function test_unknown_username_is_logged_as_invalid_username(): void
    {
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $userLogRepository = Mockery::mock(UserLogRepositoryInterface::class);
        $settingService = Mockery::mock(SystemSettingService::class);

        $userRepository
            ->shouldReceive('findByUsername')
            ->once()
            ->with('ghost')
            ->andReturn(null);

        $userLogRepository
            ->shouldReceive('createLog')
            ->once()
            ->with(
                null,
                LoginStatus::INVALID_USERNAME,
                '127.0.0.1',
                Mockery::type('string')
            )
            ->andReturn(new UserLog);

        $service = new AuthenticationService(
            $userRepository,
            $userLogRepository,
            $settingService,
            Mockery::mock(AuditLogRepositoryInterface::class),
        );

        $request = Request::create('/api/v1/auth/login', 'POST', [], [], [], [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_USER_AGENT' => 'PHPUnit',
        ]);

        $this->expectException(ValidationException::class);

        $service->login('ghost', 'secret', $request);
    }
}
