<?php

namespace App\Services\BarangayPersonnel;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\BarangayPersonnel\BarangayPersonnelRepositoryInterface;
use App\Repositories\Interfaces\BarangayPersonnel\PersonnelPositionRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Support\IdenticalWriteLock;
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
        protected PunongBarangayName $punongBarangayName,
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
        return IdenticalWriteLock::acquire(IdenticalWriteLock::personnel($data), function () use ($performedBy, $data) {
            return DB::transaction(function () use ($performedBy, $data) {
                $this->assertNoUnconfirmedDuplicate($data);
                $positionId = $this->resolvePositionId($performedBy, $data);
                $this->assertPositionAvailable($positionId);

                $personnel = $this->personnelRepository->create([
                    'position_id' => $positionId,
                    'personnel_last_name' => $data['personnel_last_name'],
                    'personnel_first_name' => $data['personnel_first_name'],
                    'personnel_middle_name' => $data['personnel_middle_name'] ?? null,
                    'personnel_suffix' => $data['personnel_suffix'] ?? null,
                    'personnel_date_of_birth' => $data['personnel_date_of_birth'],
                    'sex_id' => $data['sex_id'],
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

                if ($this->punongBarangayName->isPunong($personnel)) {
                    $this->punongBarangayName->alignSettingWithPersonnel($performedBy);
                }

                return $this->formatRecord($personnel);
            });
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updatePersonnel(User $performedBy, BarangayPersonnel $personnel, array $data): array
    {
        $previous = $this->personnelAuditSnapshot($personnel);

        return IdenticalWriteLock::acquire(IdenticalWriteLock::personnel($data), function () use ($performedBy, $personnel, $data, $previous) {
            return DB::transaction(function () use ($performedBy, $personnel, $data, $previous) {
                $this->assertNoUnconfirmedDuplicate($data, $personnel->personnel_id);
                $wasPunong = $this->punongBarangayName->isPunong($personnel);

                if (! empty($data['position_id'])) {
                    $this->assertPositionAvailable((int) $data['position_id'], $personnel->personnel_id);
                }

                unset($data['confirm_duplicate'], $data['position_name'], $data['admin_password']);

                $updated = $this->personnelRepository->update($personnel, $data);

                $this->logPersonnelFieldChanges($performedBy, $updated, $previous);

                if ($wasPunong || $this->punongBarangayName->isPunong($updated)) {
                    $this->punongBarangayName->alignSettingWithPersonnel($performedBy);
                }

                return $this->formatRecord($updated);
            });
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(BarangayPersonnel $personnel): array
    {
        $personnel->loadMissing(['position', 'status', 'user', 'sex']);

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
            'sex_id' => $personnel->sex_id,
            'sex' => $personnel->sex?->sex,
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
            'position_name' => PersonnelPosition::standardizeName($data['position_name']),
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

    /**
     * @return array{name: string, birth date: string, sex: string, status: string, position: string}
     */
    private function personnelAuditSnapshot(BarangayPersonnel $personnel): array
    {
        $personnel->loadMissing(['position', 'status', 'sex']);

        return [
            'name' => $this->fullName($personnel),
            'birth date' => $personnel->personnel_date_of_birth?->format('Y-m-d') ?? '',
            'sex' => (string) ($personnel->sex?->sex ?? ''),
            'status' => (string) ($personnel->status?->personnel_status ?? $personnel->personnel_status_id),
            'position' => (string) ($personnel->position?->position_name ?? $personnel->position_id),
        ];
    }

    /**
     * @param  array{name: string, birth date: string, sex: string, status: string, position: string}  $previous
     */
    private function logPersonnelFieldChanges(User $performedBy, BarangayPersonnel $updated, array $previous): void
    {
        $current = $this->personnelAuditSnapshot($updated);

        foreach ($current as $target => $newValue) {
            $oldValue = $previous[$target] ?? '';

            if ($oldValue === $newValue) {
                continue;
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $updated->personnel_id,
                description: 'Updated barangay personnel '.$target,
                oldValue: $oldValue,
                newValue: $newValue,
                target: $target,
                entity: 'barangay_personnel',
            );
        }
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
