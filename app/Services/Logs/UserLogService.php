<?php

namespace App\Services\Logs;

use App\Models\Logs\UserLog;
use App\Repositories\Interfaces\Logs\UserLogRepositoryInterface;
use Carbon\Carbon;

class UserLogService
{
    public function __construct(
        protected UserLogRepositoryInterface $userLogRepository,
    ) {}

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

    private function formatTimestamp(?Carbon $timestamp): ?string
    {
        return $timestamp?->timezone(config('app.timezone'))->format('Y-m-d H:i:s');
    }
}
