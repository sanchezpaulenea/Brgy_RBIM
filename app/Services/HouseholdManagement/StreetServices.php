<?php

namespace App\Services\HouseholdManagement;

use App\Models\HouseholdManagement\Street;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\HouseholdManagement\StreetRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Support\IdenticalWriteLock;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class StreetServices
{
    public function __construct(
        protected StreetRepositoryInterface $streetRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listStreets(): array
    {
        return $this->streetRepository
            ->all()
            ->map(fn (Street $street) => $this->streetRepository->formatRecord($street))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createStreet(User $performedBy, array $data): array
    {
        return IdenticalWriteLock::acquire('street:'.mb_strtolower(trim((string) $data['street_name'])), function () use ($performedBy, $data) {
            return DB::transaction(function () use ($performedBy, $data) {
                $data['street_name'] = Street::standardizeName($data['street_name']);

                if (Street::query()->whereRaw('LOWER(street_name) = ?', [mb_strtolower($data['street_name'])])->exists()) {
                    throw ValidationException::withMessages([
                        'street_name' => ['This street name already exists.'],
                    ]);
                }

                try {
                    $street = $this->streetRepository->create($data);
                } catch (UniqueConstraintViolationException) {
                    throw ValidationException::withMessages([
                        'street_name' => ['This street name already exists.'],
                    ]);
                }

                $this->auditLogRepository->log(
                    performedByUserId: $performedBy->user_id,
                    actionId: Action::CREATE,
                    recordId: $street->street_id,
                    description: 'Create street',
                    oldValue: null,
                    newValue: $street->street_name,
                    target: 'street_name',
                    entity: 'street',
                );

                return $this->streetRepository->formatRecord($street);
            });
        });
    }

    public function deleteStreet(User $performedBy, Street $street): void
    {
        $label = $street->street_name;
        $streetId = $street->street_id;

        DB::transaction(function () use ($performedBy, $label, $streetId) {
            $locked = $this->streetRepository->lockById($streetId);

            if ($locked === null) {
                throw new ModelNotFoundException("Street [{$streetId}] not found.");
            }

            if ($this->streetRepository->isInUse($locked)) {
                throw new ConflictHttpException(
                    'This street is assigned to one or more households and cannot be deleted.'
                );
            }

            try {
                if (! $this->streetRepository->delete($locked)) {
                    throw new ModelNotFoundException("Street [{$streetId}] not found.");
                }
            } catch (QueryException $e) {
                if (! $this->isForeignKeyViolation($e)) {
                    throw $e;
                }

                throw new ConflictHttpException(
                    'This street is assigned to one or more households and cannot be deleted.'
                );
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $streetId,
                description: 'Delete street',
                oldValue: $label,
                newValue: '',
                target: 'street_name',
                entity: 'street',
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
