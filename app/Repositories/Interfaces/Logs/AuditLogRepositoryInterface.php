<?php

namespace App\Repositories\Interfaces\Logs;

use App\Models\Logs\AuditLog;
use App\Models\Logs\AuditLogArchive;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface AuditLogRepositoryInterface
{
    public function log(
        int $performedByUserId,
        int $actionId,
        int $recordId,
        string $description,
        ?string $oldValue,
        string $newValue,
        string $target,
        string $entity,
    ): AuditLog;

    /**
     * @param  array{user_id?: int, username?: string, entity?: string, target?: string, date_from?: string, date_to?: string, source?: string}  $filters
     * @return Collection<int, AuditLog|AuditLogArchive>
     */
    public function list(array $filters = []): Collection;

    public function hasEntriesForUser(int $userId): bool;

    public function getLastUserStatusChangeTime(int $userId, int $userStatusId): ?Carbon;
}
