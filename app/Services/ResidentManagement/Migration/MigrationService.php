<?php

namespace App\Services\ResidentManagement\Migration;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Migration\Migration;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Migration\MigrationRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MigrationService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected MigrationRepositoryInterface $migrationRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Resident $resident, array $data): array
    {
        if ($this->migrationRepository->findByResidentId($resident->resident_id) !== null) {
            throw ValidationException::withMessages([
                'migration' => ['A migration record already exists for this resident.'],
            ]);
        }

        $data['resident_id'] = $resident->resident_id;

        return DB::transaction(function () use ($performedBy, $data) {
            $migration = $this->migrationRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $migration->migration_id,
                description: 'Create migration',
                oldValue: null,
                newValue: (string) ($migration->reasonForLeaving?->reason_for_leaving ?? $migration->reason_for_leaving_id),
                target: 'record',
                entity: 'migration',
            );

            return $this->formatRecord($migration);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, Migration $migration, array $data): array
    {
        $previous = $this->auditSnapshot($migration);

        return DB::transaction(function () use ($performedBy, $migration, $data, $previous) {
            $updated = $this->migrationRepository->update($migration, $data);

            $this->logFieldChanges(
                $performedBy,
                $updated->migration_id,
                'migration',
                $previous,
                $this->auditSnapshot($updated),
                'Updated migration',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Migration $migration): array
    {
        $migration->loadMissing(['reasonForLeaving', 'reasonForTransfer']);

        return [
            'migration_id' => $migration->migration_id,
            'resident_id' => $migration->resident_id,
            'previous_residence_6mos_brgy' => $migration->previous_residence_6mos_brgy,
            'previous_residence_6mos_city_municipality' => $migration->previous_residence_6mos_city_municipality,
            'previous_residence_5yrs_brgy' => $migration->previous_residence_5yrs_brgy,
            'previous_residence_5yrs_city_municipality' => $migration->previous_residence_5yrs_city_municipality,
            'date_of_transfer_in_brgy' => $migration->date_of_transfer_in_brgy?->format('Y-m-d'),
            'reason_for_leaving_id' => $migration->reason_for_leaving_id,
            'reason_for_leaving' => $migration->reasonForLeaving?->reason_for_leaving,
            'will_return_to_previous_residence' => (bool) $migration->will_return_to_previous_residence,
            'reason_for_transfer_id' => $migration->reason_for_transfer_id,
            'reason_for_transfer' => $migration->reasonForTransfer?->reason_for_transfer,
            'duration_of_stay' => $migration->duration_of_stay?->format('Y-m-d'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(Migration $migration): array
    {
        $migration->loadMissing(['reasonForLeaving', 'reasonForTransfer']);

        return [
            'previous residence 6mos brgy' => (string) ($migration->previous_residence_6mos_brgy ?? ''),
            'previous residence 6mos city' => (string) ($migration->previous_residence_6mos_city_municipality ?? ''),
            'previous residence 5yrs brgy' => (string) ($migration->previous_residence_5yrs_brgy ?? ''),
            'previous residence 5yrs city' => (string) ($migration->previous_residence_5yrs_city_municipality ?? ''),
            'date of transfer' => $migration->date_of_transfer_in_brgy?->format('Y-m-d') ?? '',
            'reason for leaving' => (string) ($migration->reasonForLeaving?->reason_for_leaving ?? $migration->reason_for_leaving_id),
            'will return' => $migration->will_return_to_previous_residence ? 'Yes' : 'No',
            'reason for transfer' => (string) ($migration->reasonForTransfer?->reason_for_transfer ?? $migration->reason_for_transfer_id),
            'duration of stay' => $migration->duration_of_stay?->format('Y-m-d') ?? '',
        ];
    }
}
