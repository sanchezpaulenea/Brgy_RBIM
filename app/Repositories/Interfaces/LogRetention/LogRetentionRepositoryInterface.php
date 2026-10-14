<?php

namespace App\Repositories\Interfaces\LogRetention;

interface LogRetentionRepositoryInterface
{
    /**
     * @return array{live_count: int, archived_count: int, oldest_record: ?string}
     */
    public function counts(string $log): array;

    public function previewCount(string $log, string $cutoff): int;

    /**
     * Move eligible rows into the archive table and delete those same rows.
     */
    public function archiveExpired(string $log, string $cutoff, int $archivedBy): int;
}
