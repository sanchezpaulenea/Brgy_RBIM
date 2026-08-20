<?php

namespace App\Services\BarangayPersonnel;

use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\BarangayPersonnel\PersonnelPositionRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PersonnelPositionService
{
    public function __construct(
        protected PersonnelPositionRepositoryInterface $personnelPositionRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listPositions(): array
    {
        return $this->personnelPositionRepository
            ->all()
            ->map(fn (PersonnelPosition $position) => $this->personnelPositionRepository->formatRecord($position))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createPosition(User $performedBy, array $data): array
    {
        return DB::transaction(function () use ($performedBy, $data) {
            $position = $this->personnelPositionRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $position->position_id,
                description: 'Create personnel position',
                oldValue: null,
                newValue: $position->position_name,
                target: 'position_name',
                entity: 'personnel_position',
            );

            return $this->personnelPositionRepository->formatRecord($position);
        });
    }

    public function deletePosition(User $performedBy, PersonnelPosition $position): void
    {
        $label = $position->position_name;
        $positionId = $position->position_id;

        DB::transaction(function () use ($performedBy, $label, $positionId) {
            $locked = $this->personnelPositionRepository->lockById($positionId);

            if ($locked === null) {
                throw new ModelNotFoundException("Personnel position [{$positionId}] not found.");
            }

            if ($this->personnelPositionRepository->isInUse($locked)) {
                throw new ConflictHttpException(
                    'This personnel position is assigned to one or more personnel records and cannot be deleted.'
                );
            }

            try {
                if (! $this->personnelPositionRepository->delete($locked)) {
                    throw new ModelNotFoundException("Personnel position [{$positionId}] not found.");
                }
            } catch (QueryException $e) {
                if (! $this->isForeignKeyViolation($e)) {
                    throw $e;
                }

                throw new ConflictHttpException(
                    'This personnel position is assigned to one or more personnel records and cannot be deleted.'
                );
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $positionId,
                description: 'Delete personnel position',
                oldValue: $label,
                newValue: '',
                target: 'position_name',
                entity: 'personnel_position',
            );
        });
    }

    private function isForeignKeyViolation(QueryException $e): bool
    {
        return $e->getCode() === 23000
            || $e->getCode() === '23000'
            || ($e->errorInfo[0] ?? null) === '23000';
    }
}
