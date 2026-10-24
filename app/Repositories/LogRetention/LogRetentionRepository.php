<?php

namespace App\Repositories\LogRetention;

use App\Repositories\Interfaces\LogRetention\LogRetentionRepositoryInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class LogRetentionRepository implements LogRetentionRepositoryInterface
{
    /**
     * @return array{live_count: int, archived_count: int, oldest_record: ?string}
     */
    public function counts(string $log): array
    {
        [$live, $archive, $oldestColumn] = $this->tables($log);

        $oldest = DB::table($live)->min($oldestColumn);

        return [
            'live_count' => DB::table($live)->count(),
            'archived_count' => DB::table($archive)->count(),
            'oldest_record' => $oldest === null ? null : (string) $oldest,
        ];
    }

    public function previewCount(string $log, string $cutoff): int
    {
        return $this->eligible($log, $cutoff, $this->maxLiveId($log))->count();
    }

    public function archiveExpired(string $log, string $cutoff, int $archivedBy): int
    {
        $maxId = $this->maxLiveId($log);
        $eligible = $this->eligible($log, $cutoff, $maxId);
        $count = (clone $eligible)->count();

        if ($count === 0) {
            return 0;
        }

        $moved = 0;
        $key = $log === 'audit' ? 'audit_id' : 'user_log_id';
        $archivedAt = now()->format('Y-m-d H:i:s');

        do {
            $rows = (clone $eligible)
                ->orderBy($key)
                ->limit(200)
                ->get($this->liveColumns($log));

            if ($rows->isEmpty()) {
                break;
            }

            $payload = [];
            $ids = [];

            foreach ($rows as $row) {
                $record = (array) $row;
                $ids[] = $record[$key];
                $record['archived_at'] = $archivedAt;
                $record['archived_by'] = $archivedBy;
                $payload[] = $record;
            }

            DB::table($this->archiveTable($log))->insert($payload);

            $deleted = DB::table($this->tables($log)[0])->whereIn($key, $ids)->delete();

            if ($deleted !== count($ids)) {
                throw new RuntimeException('Archive move did not finish cleanly.');
            }

            $moved += $deleted;
        } while (count($ids) === 200);

        if ($moved !== $count) {
            throw new RuntimeException('Archive move did not finish cleanly.');
        }

        return $moved;
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function tables(string $log): array
    {
        return match ($log) {
            'audit' => ['audit_log', 'audit_log_archive', 'performed_at'],
            'user' => ['user_log', 'user_log_archive', 'login_time'],
            default => throw new InvalidArgumentException('Log must be audit or user.'),
        };
    }

    private function archiveTable(string $log): string
    {
        return $this->tables($log)[1];
    }

    private function maxLiveId(string $log): int
    {
        $table = $this->tables($log)[0];
        $key = $log === 'audit' ? 'audit_id' : 'user_log_id';

        return (int) (DB::table($table)->max($key) ?? 0);
    }

    private function eligible(string $log, string $cutoff, int $maxId): Builder
    {
        if ($log === 'audit') {
            return DB::table('audit_log')
                ->where('audit_id', '<=', $maxId)
                ->where('performed_at', '<', $cutoff);
        }

        if ($log === 'user') {
            return DB::table('user_log')
                ->where('user_log_id', '<=', $maxId)
                ->where('login_time', '<', $cutoff)
                ->whereNotNull('logout_time');
        }

        throw new InvalidArgumentException('Log must be audit or user.');
    }

    /**
     * @return array<int, string>
     */
    private function liveColumns(string $log): array
    {
        return match ($log) {
            'audit' => [
                'audit_id',
                'user_id',
                'action_id',
                'record_id',
                'description',
                'old_value',
                'new_value',
                'performed_at',
                'target',
                'entity',
            ],
            'user' => [
                'user_log_id',
                'user_id',
                'login_time',
                'logout_time',
                'login_status_id',
                'ip_address',
                'device',
            ],
            default => throw new InvalidArgumentException('Log must be audit or user.'),
        };
    }
}
