<?php

namespace App\Services\ResidentManagement\Demographic;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Ethnicity;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\EthnicityRepositoryInterface;
use App\Support\IdenticalWriteLock;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class EthnicityServices
{
    public function __construct(
        protected EthnicityRepositoryInterface $ethnicityRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listEthnicities(): array
    {
        return $this->ethnicityRepository
            ->all()
            ->map(fn (Ethnicity $ethnicity) => $this->ethnicityRepository->formatRecord($ethnicity))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createEthnicity(User $performedBy, array $data): array
    {
        return IdenticalWriteLock::acquire('ethnicity:'.mb_strtolower(trim((string) $data['ethnicity'])), function () use ($performedBy, $data) {
            return DB::transaction(function () use ($performedBy, $data) {
                $data['ethnicity'] = Ethnicity::standardizeName($data['ethnicity']);

                if (Ethnicity::query()->whereRaw('LOWER(ethnicity) = ?', [mb_strtolower($data['ethnicity'])])->exists()) {
                    throw ValidationException::withMessages([
                        'ethnicity' => ['This ethnicity already exists.'],
                    ]);
                }

                try {
                    $ethnicity = $this->ethnicityRepository->create($data);
                } catch (UniqueConstraintViolationException) {
                    throw ValidationException::withMessages([
                        'ethnicity' => ['This ethnicity already exists.'],
                    ]);
                }

                $this->auditLogRepository->log(
                    performedByUserId: $performedBy->user_id,
                    actionId: Action::CREATE,
                    recordId: $ethnicity->ethnicity_id,
                    description: 'Create ethnicity',
                    oldValue: null,
                    newValue: $ethnicity->ethnicity,
                    target: 'ethnicity',
                    entity: 'ethnicity',
                );

                return $this->ethnicityRepository->formatRecord($ethnicity);
            });
        });
    }

    public function deleteEthnicity(User $performedBy, Ethnicity $ethnicity): void
    {
        $label = $ethnicity->ethnicity;
        $ethnicityId = $ethnicity->ethnicity_id;

        DB::transaction(function () use ($performedBy, $label, $ethnicityId) {
            $locked = $this->ethnicityRepository->lockById($ethnicityId);

            if ($locked === null) {
                throw new ModelNotFoundException("Ethnicity [{$ethnicityId}] not found.");
            }

            if ($this->ethnicityRepository->isInUse($locked)) {
                throw new ConflictHttpException(
                    'This ethnicity is assigned to one or more residents and cannot be deleted.'
                );
            }

            try {
                if (! $this->ethnicityRepository->delete($locked)) {
                    throw new ModelNotFoundException("Ethnicity [{$ethnicityId}] not found.");
                }
            } catch (QueryException $e) {
                if (! $this->isForeignKeyViolation($e)) {
                    throw $e;
                }

                throw new ConflictHttpException(
                    'This ethnicity is assigned to one or more residents and cannot be deleted.'
                );
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $ethnicityId,
                description: 'Delete ethnicity',
                oldValue: $label,
                newValue: '',
                target: 'ethnicity',
                entity: 'ethnicity',
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
