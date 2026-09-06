<?php

namespace App\Services\ResidentManagement\Concerns;

use App\Models\Logs\Action;
use App\Models\UserManagement\User;

trait LogsAuditableFieldChanges
{
    /**
     * @param  array<string, string>  $previous
     * @param  array<string, string>  $current
     */
    protected function logFieldChanges(
        User $performedBy,
        int $recordId,
        string $entity,
        array $previous,
        array $current,
        string $descriptionPrefix,
    ): void {
        foreach ($current as $target => $newValue) {
            $oldValue = $previous[$target] ?? '';

            if ($oldValue === $newValue) {
                continue;
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $recordId,
                description: $descriptionPrefix.' '.$target,
                oldValue: $oldValue,
                newValue: $newValue,
                target: $target,
                entity: $entity,
            );
        }
    }
}
