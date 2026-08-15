<?php

namespace App\Services\BarangayPersonnel;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\BarangayPersonnel\BarangayPersonnelRepositoryInterface;
use App\Repositories\Interfaces\BarangayPersonnel\PersonnelPositionRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BarangayPersonnelService
{
    public const ACTIVE_STATUS_ID = 1;

    public const DUPLICATE_MESSAGE = 'A personnel record with the same name and date of birth already exists. Please verify before continuing.';

    public function __construct(
        protected BarangayPersonnelRepositoryInterface $personnelRepository,
        protected PersonnelPositionRepositoryInterface $personnelPositionRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listPersonnel(): array
    {
        return $this->personnelRepository
            ->all()
            ->map(fn (BarangayPersonnel $personnel) => $this->formatRecord($personnel))
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createPersonnel(User $performedBy, array $data): array
    {
        $this->assertNoUnconfirmedDuplicate($data);

        return DB::transaction(function () use ($performedBy, $data) {
            $positionId = $this->resolvePositionId($performedBy, $data);
            $this->assertPositionAvailable($positionId);

            $personnel = $this->personnelRepository->create([
                'position_id' => $positionId,
                'personnel_last_name' => $data['personnel_last_name'],
                'personnel_first_name' => $data['personnel_first_name'],
                'personnel_middle_name' => $data['personnel_middle_name'] ?? null,
                'personnel_suffix' => $data['personnel_suffix'] ?? null,
                'personnel_date_of_birth' => $data['personnel_date_of_birth'],
                'personnel_status_id' => $data['personnel_status_id'] ?? self::ACTIVE_STATUS_ID,
            ]);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $personnel->personnel_id,
                description: 'Create barangay personnel',
                oldValue: null,
                newValue: $this->fullName($personnel),
                target: 'record',
                entity: 'barangay_personnel',
            );

            return $this->formatRecord($personnel);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updatePersonnel(User $performedBy, BarangayPersonnel $personnel, array $data): array
    {
        $this->assertNoUnconfirmedDuplicate($data, $personnel->personnel_id);

        $oldName = $this->fullName($personnel);

        return DB::transaction(function () use ($performedBy, $personnel, $data, $oldName) {
            if (! empty($data['position_id'])) {
                $this->assertPositionAvailable((int) $data['position_id'], $personnel->personnel_id);
            }

            unset($data['confirm_duplicate'], $data['position_name']);

            $updated = $this->personnelRepository->update($personnel, $data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $updated->personnel_id,
                description: 'Update barangay personnel',
                oldValue: $oldName,
                newValue: $this->fullName($updated),
                target: 'record',
                entity: 'barangay_personnel',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(BarangayPersonnel $personnel): array
    {
        $personnel->loadMissing(['position', 'status', 'user']);

        $fullName = $this->fullName($personnel);

        return [
            'personnel_id' => $personnel->personnel_id,
            'position_id' => $personnel->position_id,
            'position_name' => $personnel->position?->position_name,
            'personnel_last_name' => $personnel->personnel_last_name,
            'personnel_first_name' => $personnel->personnel_first_name,
            'personnel_middle_name' => $personnel->personnel_middle_name,
            'personnel_suffix' => $personnel->personnel_suffix,
            'personnel_status_id' => $personnel->personnel_status_id,
            'personnel_status' => $personnel->status?->personnel_status,
            'personnel_date_of_birth' => $personnel->personnel_date_of_birth?->format('Y-m-d'),
            'username' => $personnel->user?->username,
            'linked' => $personnel->user !== null,
            'full_name' => $fullName,
            'label' => $fullName,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertNoUnconfirmedDuplicate(array $data, ?int $excludePersonnelId = null): void
    {
        if (! empty($data['confirm_duplicate'])) {
            return;
        }

        $match = $this->personnelRepository->findMatchingIdentity(
            $data['personnel_last_name'],
            $data['personnel_first_name'],
            $data['personnel_middle_name'] ?? null,
            $data['personnel_suffix'] ?? null,
            (string) $data['personnel_date_of_birth'],
            $excludePersonnelId,
        );

        if ($match !== null) {
            throw ValidationException::withMessages([
                'duplicate' => [self::DUPLICATE_MESSAGE],
            ]);
        }
    }

    private function assertPositionAvailable(int $positionId, ?int $excludePersonnelId = null): void
    {
        $position = $this->personnelPositionRepository->findById($positionId);

        if ($position === null) {
            return;
        }

        $position->load(['personnel' => fn ($query) => $query->where('personnel_status_id', self::ACTIVE_STATUS_ID)]);
        $holder = $position->personnel->first();

        if ($holder !== null && $holder->personnel_id !== $excludePersonnelId) {
            throw ValidationException::withMessages([
                'position_id' => ['This position is already held by an active barangay personnel.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolvePositionId(User $performedBy, array $data): int
    {
        if (! empty($data['position_id'])) {
            return (int) $data['position_id'];
        }

        $position = $this->personnelPositionRepository->create([
            'position_name' => $data['position_name'],
        ]);

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

        return $position->position_id;
    }

    private function fullName(BarangayPersonnel $personnel): string
    {
        $givenNames = collect([
            $personnel->personnel_first_name,
            $personnel->personnel_middle_name,
            $personnel->personnel_suffix,
        ])->filter()->implode(' ');

        if ($givenNames === '') {
            return $personnel->personnel_last_name;
        }

        return $personnel->personnel_last_name.', '.$givenNames;
    }
}
