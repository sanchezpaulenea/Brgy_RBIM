<?php

namespace App\Services\LogRetention;

use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\LogRetention\LogRetentionRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Setting\SettingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LogRetentionService
{
    public function __construct(
        protected LogRetentionRepositoryInterface $logRetentionRepository,
        protected SettingRepositoryInterface $settingRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array{audit: array<string, mixed>, user: array<string, mixed>}
     */
    public function summary(): array
    {
        return $this->guard(fn (): array => [
            'audit' => $this->logSummary('audit', 'audit_log_retention_days'),
            'user' => $this->logSummary('user', 'user_log_retention_days'),
        ]);
    }

    /**
     * @param  array{audit_log_retention_days: int, user_log_retention_days: int}  $days
     * @return array{changed: int, audit: array<string, mixed>, user: array<string, mixed>}
     */
    public function update(User $performedBy, array $days): array
    {
        return $this->guard(function () use ($performedBy, $days): array {
            $changed = DB::transaction(function () use ($performedBy, $days): int {
                $writes = 0;

                foreach ([
                    'audit_log_retention_days' => $days['audit_log_retention_days'],
                    'user_log_retention_days' => $days['user_log_retention_days'],
                ] as $key => $value) {
                    if ($this->updateRetentionDays($performedBy, $key, (int) $value)) {
                        $writes++;
                    }
                }

                return $writes;
            });

            $summary = [
                'audit' => $this->logSummary('audit', 'audit_log_retention_days'),
                'user' => $this->logSummary('user', 'user_log_retention_days'),
            ];

            return [
                'changed' => $changed,
                ...$summary,
            ];
        });
    }

    public function preview(string $log): int
    {
        $this->assertLog($log);

        return $this->guard(fn (): int => $this->logRetentionRepository->previewCount(
            $log,
            $this->cutoffFor($log),
        ));
    }

    public function archive(User $performedBy, string $log): int
    {
        $this->assertLog($log);

        $lock = Cache::lock('log-retention-archive', 120);

        if (! $lock->get()) {
            throw new ConflictHttpException('An archive is already running. Wait for it to finish, then try again.');
        }

        try {
            try {
                $moved = DB::transaction(fn (): int => $this->logRetentionRepository->archiveExpired(
                    $log,
                    $this->cutoffFor($log),
                    $performedBy->user_id,
                ));
            } catch (QueryException|RuntimeException $exception) {
                report($exception);

                throw new HttpException(500, 'The archive could not be completed. Please try again.');
            }

            try {
                $this->recordArchive($performedBy, $log, $moved);
            } catch (QueryException $exception) {
                report($exception);

                throw new HttpException(500, 'The records were archived, but the activity entry could not be saved.');
            }

            return $moved;
        } finally {
            $lock->release();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function logSummary(string $log, string $settingKey): array
    {
        $setting = $this->settingRepository->findByKey($settingKey);
        $counts = $this->logRetentionRepository->counts($log);

        return [
            'retention_days' => $setting === null ? null : (int) $setting->setting_value,
            'live_count' => $counts['live_count'],
            'archived_count' => $counts['archived_count'],
            'oldest_record' => $counts['oldest_record'],
        ];
    }

    private function updateRetentionDays(User $performedBy, string $key, int $days): bool
    {
        $setting = $this->settingRepository->findByKey($key);

        if ($setting === null) {
            throw ValidationException::withMessages([
                $key => 'This retention setting is not configured.',
            ]);
        }

        $oldValue = (string) $setting->setting_value;
        $newValue = (string) $days;

        if ($oldValue === $newValue) {
            return false;
        }

        $updated = $this->settingRepository->update($setting, $newValue);

        $this->auditLogRepository->log(
            performedByUserId: $performedBy->user_id,
            actionId: Action::UPDATE,
            recordId: $updated->setting_id,
            description: 'Update log retention',
            oldValue: $oldValue,
            newValue: $updated->setting_value,
            target: $key,
            entity: 'system_setting',
        );

        return true;
    }

    private function recordArchive(User $performedBy, string $log, int $moved): void
    {
        $this->auditLogRepository->log(
            performedByUserId: $performedBy->user_id,
            actionId: Action::UPDATE,
            recordId: 0,
            description: $log === 'audit' ? 'Archive audit log records' : 'Archive user log records',
            oldValue: null,
            newValue: $moved.' records',
            target: 'archive',
            entity: $log === 'audit' ? 'audit_log' : 'user_log',
        );
    }

    private function cutoffFor(string $log): string
    {
        $key = $log === 'audit' ? 'audit_log_retention_days' : 'user_log_retention_days';

        return Carbon::now(config('app.timezone'))
            ->subDays($this->requireRetentionDays($key))
            ->format('Y-m-d H:i:s');
    }

    private function requireRetentionDays(string $key): int
    {
        $setting = $this->settingRepository->findByKey($key);

        if ($setting === null || ! is_numeric($setting->setting_value)) {
            throw ValidationException::withMessages([
                'log' => 'The retention period is not configured.',
            ]);
        }

        return (int) $setting->setting_value;
    }

    private function assertLog(string $log): void
    {
        if (! in_array($log, ['audit', 'user'], true)) {
            throw ValidationException::withMessages([
                'log' => 'Log must be audit or user.',
            ]);
        }
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    private function guard(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (QueryException $exception) {
            report($exception);

            throw new HttpException(500, 'Log retention could not be completed. Please try again.');
        }
    }
}
