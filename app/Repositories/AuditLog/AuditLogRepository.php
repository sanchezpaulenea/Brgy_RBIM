<?php

namespace App\Repositories\AuditLog;

use App\Models\AuditLog\AuditLog;
use App\Repositories\Interfaces\AuditLog\AuditLogRepositoryInterface;

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
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'performed_at' => now()->toDateTimeString(),
            'target' => $target,
            'entity' => $entity,
        ]);
    }
}
