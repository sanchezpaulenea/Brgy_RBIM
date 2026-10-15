<?php

namespace App\Services\ResidentManagement\Migration;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\ResidentType;
use App\Models\ResidentManagement\Migration\Migration;
use App\Models\ResidentManagement\Migration\ReasonForLeaving;
use App\Models\ResidentManagement\Migration\ReasonForTransfer;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Migration\MigrationRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use App\Services\ResidentManagement\Concerns\SerializesResidentSectionWrites;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MigrationService
{
    use LogsAuditableFieldChanges;
    use SerializesResidentSectionWrites;

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
        $data = $this->applyBusinessRules($data, null, $resident);
        $data['resident_id'] = $resident->resident_id;

        return $this->withResidentLock($resident->resident_id, function () use ($performedBy, $resident, $data) {
            if ($this->migrationRepository->findByResidentId($resident->resident_id) !== null) {
                throw ValidationException::withMessages([
                    'migration' => ['A migration record already exists for this resident.'],
                ]);
            }

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
        $migration->loadMissing('resident');
        $data = $this->applyBusinessRules($data, $migration, $migration->resident);

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

        $nonMigrant = MigrationClassifier::isNonMigrant((int) $migration->resident_type_id);
        $transferDate = $this->storedDate($migration, 'date_of_transfer_in_brgy');
        $stayMonths = MigrationClassifier::lengthOfStayMonths($transferDate);
        $willReturn = $nonMigrant ? null : $migration->will_return_to_previous_residence;

        return [
            'migration_id' => $migration->migration_id,
            'resident_id' => $migration->resident_id,
            'since_birth' => $nonMigrant,
            'date_of_transfer_in_brgy' => $transferDate,
            'length_of_stay_months' => $stayMonths,
            'length_of_stay_label' => MigrationClassifier::lengthOfStayLabel($stayMonths),
            'resident_type_id' => $migration->resident_type_id,
            'resident_type' => $migration->residentType?->resident_type,
            'reason_for_leaving_id' => $nonMigrant ? null : $migration->reason_for_leaving_id,
            'reason_for_leaving' => $nonMigrant ? null : $migration->reasonForLeaving?->reason_for_leaving,
            'will_return_to_previous_residence' => $willReturn,
            'reason_for_transfer_id' => $nonMigrant ? null : $migration->reason_for_transfer_id,
            'reason_for_transfer' => $nonMigrant ? null : $migration->reasonForTransfer?->reason_for_transfer,
            'duration_of_stay' => $willReturn === true
                ? $this->storedDate($migration, 'duration_of_stay')
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyBusinessRules(array $data, ?Migration $existing = null, ?Resident $resident = null): array
    {
        $sinceBirth = $this->resolveSinceBirth($data, $existing);
        unset(
            $data['since_birth'],
            $data['resident_type_id'],
            $data['length_of_stay_months'],
            $data['length_of_stay_label'],
        );

        $merged = $this->mergedAttributes($data, $existing);

        if ($sinceBirth) {
            $birthDate = $resident?->date_of_birth?->toDateString();

            if ($birthDate === null) {
                throw ValidationException::withMessages([
                    'date_of_transfer_in_brgy' => 'Enter the date of birth in demographics before marking Since Birth.',
                ]);
            }

            $data['date_of_transfer_in_brgy'] = $birthDate;
            $data['duration_of_stay'] = null;
            $data['reason_for_leaving_id'] = ReasonForLeaving::ensureNotApplicableId();
            $data['reason_for_transfer_id'] = ReasonForTransfer::ensureNotApplicableId();
            $data['will_return_to_previous_residence'] = false;
            $data['resident_type_id'] = ResidentType::NON_MIGRANT;

            return $data;
        }

        $transferDate = MigrationClassifier::normalizeTransferDate($merged['date_of_transfer_in_brgy'] ?? null);
        $stayMonths = MigrationClassifier::lengthOfStayMonths($transferDate);

        if ($stayMonths === null) {
            throw ValidationException::withMessages([
                'date_of_transfer_in_brgy' => 'Date of transfer is required.',
            ]);
        }

        $this->assertMigrantFields($merged);

        $willReturn = filter_var($merged['will_return_to_previous_residence'], FILTER_VALIDATE_BOOLEAN);
        $data['resident_type_id'] = MigrationClassifier::classify(false, $stayMonths);
        $data['date_of_transfer_in_brgy'] = $transferDate;
        $data['will_return_to_previous_residence'] = $willReturn;
        $data['duration_of_stay'] = $willReturn
            ? $this->normalizeMonthDate($merged['duration_of_stay'] ?? null)
            : null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveSinceBirth(array $data, ?Migration $existing): bool
    {
        if (array_key_exists('since_birth', $data)) {
            return filter_var($data['since_birth'], FILTER_VALIDATE_BOOLEAN);
        }

        return $existing !== null
            && MigrationClassifier::isNonMigrant((int) $existing->resident_type_id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mergedAttributes(array $data, ?Migration $existing): array
    {
        $current = $existing === null ? [] : $existing->only([
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
            $errors['date_of_transfer_in_brgy'] = 'Date of transfer is required.';
        }

        $leaving = ReasonForLeaving::query()->find((int) ($merged['reason_for_leaving_id'] ?? 0));
        if ($leaving === null || $leaving->indicatesNotApplicable()) {
            $errors['reason_for_leaving_id'] = 'Reason for leaving is required for migrants and transients.';
        }

        if (! array_key_exists('will_return_to_previous_residence', $merged)
            || $merged['will_return_to_previous_residence'] === null
            || $merged['will_return_to_previous_residence'] === '') {
            $errors['will_return_to_previous_residence'] = 'Indicate whether the resident plans to return to the previous residence.';
        }

        $transfer = ReasonForTransfer::query()->find((int) ($merged['reason_for_transfer_id'] ?? 0));
        if ($transfer === null || $transfer->indicatesNotApplicable()) {
            $errors['reason_for_transfer_id'] = 'Reason for transfer is required for migrants and transients.';
        }

        $willReturn = filter_var($merged['will_return_to_previous_residence'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $stayDate = $this->normalizeMonthDate($merged['duration_of_stay'] ?? null);

        if ($willReturn && $stayDate === null) {
            $errors['duration_of_stay'] = 'Indicate until when the resident intends to stay.';
        }

        if ($willReturn
            && $stayDate !== null
            && ! MigrationClassifier::intendStayIsAfterTransfer($stayDate, $merged['date_of_transfer_in_brgy'] ?? null)) {
            $errors['duration_of_stay'] = 'Intend of stay date must be after the date of transfer.';
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

        $nonMigrant = MigrationClassifier::isNonMigrant((int) $migration->resident_type_id);
        $transferDate = $this->storedDate($migration, 'date_of_transfer_in_brgy');
        $stayMonths = MigrationClassifier::lengthOfStayMonths($transferDate);
        $willReturn = $nonMigrant ? null : $migration->will_return_to_previous_residence;

        return [
            'since birth' => $nonMigrant ? 'Yes' : 'No',
            'date of transfer' => (string) ($transferDate ?? ''),
            'length of stay' => (string) (MigrationClassifier::lengthOfStayLabel($stayMonths) ?? ''),
            'resident type' => (string) ($migration->residentType?->resident_type ?? $migration->resident_type_id),
            'reason for leaving' => $nonMigrant ? '' : (string) ($migration->reasonForLeaving?->reason_for_leaving ?? $migration->reason_for_leaving_id ?? ''),
            'will return' => $nonMigrant ? '' : $this->yesNo($willReturn),
            'reason for transfer' => $nonMigrant ? '' : (string) ($migration->reasonForTransfer?->reason_for_transfer ?? $migration->reason_for_transfer_id ?? ''),
            'duration of stay' => $willReturn === true
                ? (string) ($this->storedDate($migration, 'duration_of_stay') ?? '')
                : '',
        ];
    }

    private function storedDate(Migration $migration, string $attribute): ?string
    {
        try {
            $value = $migration->getAttribute($attribute);
        } catch (\Throwable) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            $formatted = $value->format('Y-m-d');

            return str_starts_with($formatted, '0000') ? null : $formatted;
        }

        return null;
    }

    private function normalizeMonthDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = MigrationClassifier::normalizeTransferDate($value);

        return is_string($normalized) && $normalized !== '' ? $normalized : null;
    }

    private function yesNo(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return $value ? 'Yes' : 'No';
    }
}
