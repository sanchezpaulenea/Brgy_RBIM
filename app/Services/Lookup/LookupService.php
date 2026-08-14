<?php

namespace App\Services\Lookup;

use App\Models\AuditLog\Action;
use App\Models\UserManagement\User;
use App\Policies\Lookup\LookupPolicy;
use App\Repositories\Interfaces\AuditLog\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\Lookup\LookupRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LookupService
{
    public function __construct(
        protected LookupRepositoryInterface $lookupRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listLookups(string $type): array
    {
        $this->assertSupportedType($type);

        return $this->lookupRepository
            ->all($type)
            ->map(fn ($record) => $this->lookupRepository->formatRecord($type, $record))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createLookup(User $performedBy, string $type, array $data): array
    {
        $this->assertSupportedType($type);

        if (! $this->lookupRepository->isWritable($type)) {
            throw new InvalidArgumentException("Lookup type [{$type}] is read-only.");
        }

        return DB::transaction(function () use ($performedBy, $type, $data) {
            $record = $this->lookupRepository->create($type, $data);

            if ($type === LookupPolicy::PERSONNEL_POSITION) {
                $this->auditLogRepository->log(
                    performedByUserId: $performedBy->user_id,
                    actionId: Action::CREATE,
                    recordId: (int) $record->getKey(),
                    description: 'Create personnel position lookup',
                    oldValue: null,
                    newValue: (string) $record->getAttribute('position_name'),
                    target: 'position_name',
                    entity: 'personnel_position',
                );
            }

            return $this->lookupRepository->formatRecord($type, $record);
        });
    }

    public function deleteLookup(User $performedBy, string $type, int $id): void
    {
        $this->assertSupportedType($type);

        if (! $this->lookupRepository->isWritable($type)) {
            throw new InvalidArgumentException("Lookup type [{$type}] is read-only.");
        }

        $record = $this->lookupRepository->findById($type, $id);

        if ($record === null) {
            throw new ModelNotFoundException("Lookup record [{$id}] not found for type [{$type}].");
        }

        if ($this->lookupRepository->isInUse($type, $id)) {
            throw new ConflictHttpException('This personnel position is assigned to one or more personnel records and cannot be deleted.');
        }

        $formatted = $this->lookupRepository->formatRecord($type, $record);
        $label = (string) $formatted['label'];

        DB::transaction(function () use ($performedBy, $type, $id, $label) {
            if (! $this->lookupRepository->delete($type, $id)) {
                throw new ModelNotFoundException("Lookup record [{$id}] not found for type [{$type}].");
            }

            if ($type === LookupPolicy::PERSONNEL_POSITION) {
                $this->auditLogRepository->log(
                    performedByUserId: $performedBy->user_id,
                    actionId: Action::UPDATE,
                    recordId: $id,
                    description: 'Delete personnel position lookup',
                    oldValue: $label,
                    newValue: '',
                    target: 'position_name',
                    entity: 'personnel_position',
                );
            }
        });
    }

    public function assertSupportedType(string $type): void
    {
        if (! $this->lookupRepository->isSupported($type)) {
            throw new NotFoundHttpException("Lookup type [{$type}] is not supported.");
        }
    }
}
