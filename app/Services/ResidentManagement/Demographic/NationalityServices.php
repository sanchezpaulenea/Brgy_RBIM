<?php

namespace App\Services\ResidentManagement\Demographic;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Nationality;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\NationalityRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class NationalityServices
{
    public function __construct(
        protected NationalityRepositoryInterface $nationalityRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listNationalities(): array
    {
        return $this->nationalityRepository
            ->all()
            ->map(fn (Nationality $nationality) => $this->nationalityRepository->formatRecord($nationality))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createNationality(User $performedBy, array $data): array
    {
        return DB::transaction(function () use ($performedBy, $data) {
            $data['nationality'] = Nationality::standardizeName($data['nationality']);
            $nationality = $this->nationalityRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $nationality->nationality_id,
                description: 'Create nationality',
                oldValue: null,
                newValue: $nationality->nationality,
                target: 'nationality',
                entity: 'nationality',
            );

            return $this->nationalityRepository->formatRecord($nationality);
        });
    }

    public function deleteNationality(User $performedBy, Nationality $nationality): void
    {
        $label = $nationality->nationality;
        $nationalityId = $nationality->nationality_id;

        DB::transaction(function () use ($performedBy, $label, $nationalityId) {
            $locked = $this->nationalityRepository->lockById($nationalityId);

            if ($locked === null) {
                throw new ModelNotFoundException("Nationality [{$nationalityId}] not found.");
            }

            if ($this->nationalityRepository->isInUse($locked)) {
                throw new ConflictHttpException(
                    'This nationality is assigned to one or more residents and cannot be deleted.'
                );
            }

            try {
                if (! $this->nationalityRepository->delete($locked)) {
                    throw new ModelNotFoundException("Nationality [{$nationalityId}] not found.");
                }
            } catch (QueryException $e) {
                if (! $this->isForeignKeyViolation($e)) {
                    throw $e;
                }

                throw new ConflictHttpException(
                    'This nationality is assigned to one or more residents and cannot be deleted.'
                );
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $nationalityId,
                description: 'Delete nationality',
                oldValue: $label,
                newValue: '',
                target: 'nationality',
                entity: 'nationality',
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
