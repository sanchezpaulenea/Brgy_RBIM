<?php

namespace App\Services\ResidentManagement\Demographic;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Religion;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ReligionRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ReligionServices
{
    public function __construct(
        protected ReligionRepositoryInterface $religionRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listReligions(): array
    {
        return $this->religionRepository
            ->all()
            ->map(fn (Religion $religion) => $this->religionRepository->formatRecord($religion))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createReligion(User $performedBy, array $data): array
    {
        return DB::transaction(function () use ($performedBy, $data) {
            $data['religion'] = Religion::standardizeName($data['religion']);
            $religion = $this->religionRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $religion->religion_id,
                description: 'Create religion',
                oldValue: null,
                newValue: $religion->religion,
                target: 'religion',
                entity: 'religion',
            );

            return $this->religionRepository->formatRecord($religion);
        });
    }

    public function deleteReligion(User $performedBy, Religion $religion): void
    {
        $label = $religion->religion;
        $religionId = $religion->religion_id;

        DB::transaction(function () use ($performedBy, $label, $religionId) {
            $locked = $this->religionRepository->lockById($religionId);

            if ($locked === null) {
                throw new ModelNotFoundException("Religion [{$religionId}] not found.");
            }

            if ($this->religionRepository->isInUse($locked)) {
                throw new ConflictHttpException(
                    'This religion is assigned to one or more residents and cannot be deleted.'
                );
            }

            try {
                if (! $this->religionRepository->delete($locked)) {
                    throw new ModelNotFoundException("Religion [{$religionId}] not found.");
                }
            } catch (QueryException $e) {
                if (! $this->isForeignKeyViolation($e)) {
                    throw $e;
                }

                throw new ConflictHttpException(
                    'This religion is assigned to one or more residents and cannot be deleted.'
                );
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $religionId,
                description: 'Delete religion',
                oldValue: $label,
                newValue: '',
                target: 'religion',
                entity: 'religion',
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
