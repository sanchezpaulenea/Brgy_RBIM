<?php

namespace App\Services\AuditLog;

use App\Models\AuditLog\AuditLog;
use App\Repositories\Interfaces\AuditLog\AuditLogRepositoryInterface;

class AuditLogService
{
    public function __construct(
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array{user_id?: int, entity?: string, target?: string, date_from?: string, date_to?: string}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function listAuditLogs(array $filters = []): array
    {
        return $this->auditLogRepository
            ->list($filters)
            ->map(fn (AuditLog $log) => $this->formatAuditLog($log))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatAuditLog(AuditLog $log): array
    {
        $log->loadMissing(['user', 'action']);

        return [
            'audit_id' => $log->audit_id,
            'user_id' => $log->user_id,
            'username' => $log->user?->username,
            'action_id' => $log->action_id,
            'action' => $log->action?->action,
            'record_id' => $log->record_id,
            'description' => $log->description,
            'old_value' => $log->old_value,
            'new_value' => $log->new_value,
            'target' => $log->target,
            'entity' => $log->entity,
            'performed_at' => $log->performed_at,
        ];
    }
}
