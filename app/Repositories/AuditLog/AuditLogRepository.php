<?php

namespace App\Repositories\AuditLog;

use App\Models\AuditLog\AuditLog;
use App\Repositories\Interfaces\AuditLog\AuditLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AuditLogRepository implements AuditLogRepositoryInterface
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
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $performedByUserId,
            'action_id' => $actionId,
            'record_id' => $recordId,
            'description' => mb_substr($description, 0, 255),
            'old_value' => $oldValue === null ? null : mb_substr($oldValue, 0, 45),
            'new_value' => mb_substr($newValue, 0, 45),
            'performed_at' => now()->toDateTimeString(),
            'target' => mb_substr($target, 0, 45),
            'entity' => mb_substr($entity, 0, 45),
        ]);
    }

    /**
     * @param  array{user_id?: int, entity?: string, target?: string, date_from?: string, date_to?: string}  $filters
     * @return Collection<int, AuditLog>
     */
    public function list(array $filters = []): Collection
    {
        $query = AuditLog::query()
            ->with(['user', 'action'])
            ->orderByDesc('performed_at');

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['entity'])) {
            $query->where('entity', $filters['entity']);
        }

        if (! empty($filters['target'])) {
            $query->where('target', $filters['target']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('performed_at', '>=', $filters['date_from'].' 00:00:00');
        }

        if (! empty($filters['date_to'])) {
            $query->where('performed_at', '<=', $filters['date_to'].' 23:59:59');
        }

        return $query->get();
    }

    public function hasEntriesForUser(int $userId): bool
    {
        return AuditLog::query()->where('user_id', $userId)->exists();
    }
}
