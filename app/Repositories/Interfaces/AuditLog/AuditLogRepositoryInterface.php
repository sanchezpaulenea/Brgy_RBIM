<?php

namespace App\Repositories\Interfaces\AuditLog;

use App\Models\AuditLog\AuditLog;

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
}
