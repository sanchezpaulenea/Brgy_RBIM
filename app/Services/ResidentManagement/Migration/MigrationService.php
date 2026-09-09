<?php

namespace App\Services\ResidentManagement\Migration;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\ResidentType;
use App\Models\ResidentManagement\Migration\Migration;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Migration\MigrationRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use App\Services\SystemSetting\SystemSettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MigrationService
{
    use LogsAuditableFieldChanges;

    /**
     * @var list<string>
     */
    private const SKIP_FIELDS = [
        'date_of_transfer_in_brgy',
        'reason_for_leaving_id',
        'will_return_to_previous_residence',
        'reason_for_transfer_id',
        'duration_of_stay',
    ];

    public function __construct(
        protected MigrationRepositoryInterface $migrationRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
        protected SystemSettingService $systemSettingService,
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

        $data = $this->applyBusinessRules($data);
        $data['resident_id'] = $resident->resident_id;

        return DB::transaction(function () use ($performedBy, $data) {
            $migration = $this->migrationRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $migration->migration_id,
                description: 'Create migration',
                oldValue: null,
                newValue: (string) ($migration->residentType?->resident_type ?? $migration->resident_type_id),
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
        $data = $this->applyBusinessRules($data, $migration);

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
        $migration->loadMissing(['residentType', 'reasonForLeaving', 'reasonForTransfer']);

        $stayMonths = MigrationClassifier::isNonMigrant((int) $migration->resident_type_id)
            ? null
            : MigrationClassifier::lengthOfStayMonths($migration->date_of_transfer_in_brgy);

        return [
            'migration_id' => $migration->migration_id,
            'resident_id' => $migration->resident_id,
            'previous_residence_6mos_brgy' => $migration->previous_residence_6mos_brgy,
            'previous_residence_6mos_city_municipality' => $migration->previous_residence_6mos_city_municipality,
            'previous_residence_5yrs_brgy' => $migration->previous_residence_5yrs_brgy,
            'previous_residence_5yrs_city_municipality' => $migration->previous_residence_5yrs_city_municipality,
            'length_of_stay_months' => $stayMonths,
            'length_of_stay_label' => MigrationClassifier::lengthOfStayLabel($stayMonths),
            'resident_type_id' => $migration->resident_type_id,
            'resident_type' => $migration->residentType?->resident_type,
            'date_of_transfer_in_brgy' => $migration->date_of_transfer_in_brgy?->format('Y-m-d'),
            'reason_for_leaving_id' => $migration->reason_for_leaving_id,
            'reason_for_leaving' => $migration->reasonForLeaving?->reason_for_leaving,
            'will_return_to_previous_residence' => $migration->will_return_to_previous_residence,
            'reason_for_transfer_id' => $migration->reason_for_transfer_id,
            'reason_for_transfer' => $migration->reasonForTransfer?->reason_for_transfer,
            'duration_of_stay' => $migration->duration_of_stay?->format('Y-m-d'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyBusinessRules(array $data, ?Migration $existing = null): array
    {
        unset($data['resident_type_id'], $data['length_of_stay_months'], $data['length_of_stay_label']);

        $merged = $this->mergedAttributes($data, $existing);
        $location = $this->systemSettingService->locationProfile();
        $sameAddress = MigrationClassifier::sameAsCurrentResidence(
            $merged['previous_residence_6mos_brgy'] ?? null,
            $merged['previous_residence_6mos_city_municipality'] ?? null,
            $location['barangay'],
            $location['city'],
        );

        $stayMonths = $sameAddress
            ? null
            : MigrationClassifier::lengthOfStayMonths($merged['date_of_transfer_in_brgy'] ?? null);
        $residentTypeId = MigrationClassifier::classify($sameAddress, $stayMonths);

        if ($residentTypeId === ResidentType::NON_MIGRANT) {
            foreach (self::SKIP_FIELDS as $field) {
                $data[$field] = null;
            }

            $data['resident_type_id'] = $residentTypeId;

            return $data;
        }

        $this->assertMigrantFields($merged);

        $data['resident_type_id'] = $residentTypeId;
        $data['date_of_transfer_in_brgy'] = MigrationClassifier::normalizeTransferDate(
            $merged['date_of_transfer_in_brgy'] ?? null,
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mergedAttributes(array $data, ?Migration $existing): array
    {
        $current = $existing === null ? [] : $existing->only([
            'previous_residence_6mos_brgy',
            'previous_residence_6mos_city_municipality',
            'previous_residence_5yrs_brgy',
            'previous_residence_5yrs_city_municipality',
            'date_of_transfer_in_brgy',
            'reason_for_leaving_id',
            'will_return_to_previous_residence',
            'reason_for_transfer_id',
            'duration_of_stay',
        ]);

        return array_merge($current, $data);
    }

    /**
     * @param  array<string, mixed>  $merged
     */
    private function assertMigrantFields(array $merged): void
    {
        $errors = [];

        if (MigrationClassifier::parseTransferDate($merged['date_of_transfer_in_brgy'] ?? null) === null) {
            $errors['date_of_transfer_in_brgy'] = 'Date of transfer is required for migrants and transients.';
        }

        if ((int) ($merged['reason_for_leaving_id'] ?? 0) < 1) {
            $errors['reason_for_leaving_id'] = 'Reason for leaving is required for migrants and transients.';
        }

        if (! array_key_exists('will_return_to_previous_residence', $merged)
            || $merged['will_return_to_previous_residence'] === null
            || $merged['will_return_to_previous_residence'] === '') {
            $errors['will_return_to_previous_residence'] = 'Indicate whether the resident plans to return to the previous residence.';
        }

        if ((int) ($merged['reason_for_transfer_id'] ?? 0) < 1) {
            $errors['reason_for_transfer_id'] = 'Reason for transfer is required for migrants and transients.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(Migration $migration): array
    {
        $migration->loadMissing(['residentType', 'reasonForLeaving', 'reasonForTransfer']);

        $stayMonths = MigrationClassifier::isNonMigrant((int) $migration->resident_type_id)
            ? null
            : MigrationClassifier::lengthOfStayMonths($migration->date_of_transfer_in_brgy);

        return [
            'previous residence 6mos brgy' => (string) ($migration->previous_residence_6mos_brgy ?? ''),
            'previous residence 6mos city' => (string) ($migration->previous_residence_6mos_city_municipality ?? ''),
            'previous residence 5yrs brgy' => (string) ($migration->previous_residence_5yrs_brgy ?? ''),
            'previous residence 5yrs city' => (string) ($migration->previous_residence_5yrs_city_municipality ?? ''),
            'length of stay' => (string) (MigrationClassifier::lengthOfStayLabel($stayMonths) ?? ''),
            'resident type' => (string) ($migration->residentType?->resident_type ?? $migration->resident_type_id),
            'date of transfer' => $migration->date_of_transfer_in_brgy?->format('Y-m-d') ?? '',
            'reason for leaving' => (string) ($migration->reasonForLeaving?->reason_for_leaving ?? $migration->reason_for_leaving_id ?? ''),
            'will return' => $this->yesNo($migration->will_return_to_previous_residence),
            'reason for transfer' => (string) ($migration->reasonForTransfer?->reason_for_transfer ?? $migration->reason_for_transfer_id ?? ''),
            'duration of stay' => $migration->duration_of_stay?->format('Y-m-d') ?? '',
        ];
    }

    private function yesNo(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return $value ? 'Yes' : 'No';
    }
}
