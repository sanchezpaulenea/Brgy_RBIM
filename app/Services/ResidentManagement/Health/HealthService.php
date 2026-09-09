<?php

namespace App\Services\ResidentManagement\Health;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Health\Disability;
use App\Models\ResidentManagement\Health\Health;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\HealthRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HealthService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected HealthRepositoryInterface $healthRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Resident $resident, array $data): array
    {
        if ($this->healthRepository->findByResidentId($resident->resident_id) !== null) {
            throw ValidationException::withMessages([
                'health' => ['A health record already exists for this resident.'],
            ]);
        }

        $data['resident_id'] = $resident->resident_id;
        $data = $this->applyOptionalLookupIds($data, fillMissing: true);
        $data = $this->applyOptionalPwdId($data, fillMissing: true);

        return DB::transaction(function () use ($performedBy, $data) {
            $data = $this->mapDisabilityToId($data, required: true);
            $health = $this->healthRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $health->health_id,
                description: 'Create health',
                oldValue: null,
                newValue: (string) ($health->healthInsurance?->health_insurance ?? $health->health_insurance_id),
                target: 'record',
                entity: 'health',
            );

            return $this->formatRecord($health);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, Health $health, array $data): array
    {
        $previous = $this->auditSnapshot($health);
        $data = $this->applyOptionalLookupIds($data);
        $data = $this->applyOptionalPwdId($data);

        return DB::transaction(function () use ($performedBy, $health, $data, $previous) {
            $data = $this->mapDisabilityToId($data);
            $updated = $this->healthRepository->update($health, $data);

            $this->logFieldChanges(
                $performedBy,
                $updated->health_id,
                'health',
                $previous,
                $this->auditSnapshot($updated),
                'Updated health',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Health $health): array
    {
        $health->loadMissing([
            'healthInsurance',
            'facilityVisitedPast12Mos',
            'facilityVisitReason',
            'disabilityType',
        ]);

        return [
            'health_id' => $health->health_id,
            'resident_id' => $health->resident_id,
            'health_insurance_id' => $this->nullableLookupId($health->health_insurance_id),
            'health_insurance' => $health->health_insurance_id === Health::LOOKUP_NOT_APPLICABLE
                ? null
                : $health->healthInsurance?->health_insurance,
            'facility_visited_past_12mos_id' => $this->nullableLookupId($health->facility_visited_past_12mos_id),
            'facility_visited_past_12mos' => $health->facility_visited_past_12mos_id === Health::LOOKUP_NOT_APPLICABLE
                ? null
                : $health->facilityVisitedPast12Mos?->facility_visited_past_12mos,
            'facility_visit_reason_id' => $this->nullableLookupId($health->facility_visit_reason_id),
            'facility_visit_reason' => $health->facility_visit_reason_id === Health::LOOKUP_NOT_APPLICABLE
                ? null
                : $health->facilityVisitReason?->facility_visit_reason,
            'disability_id' => $health->disability_id,
            'disability' => $health->disabilityType?->disability,
            'pwd_id_number' => $health->pwd_id_number === Health::PWD_ID_NOT_APPLICABLE
                ? null
                : $health->pwd_id_number,
        ];
    }

    /**
     * Q26–Q28 are optional. Empty values are stored as 0 because the columns
     * are INT NOT NULL.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyOptionalLookupIds(array $data, bool $fillMissing = false): array
    {
        foreach ([
            'health_insurance_id',
            'facility_visited_past_12mos_id',
            'facility_visit_reason_id',
        ] as $field) {
            if (! array_key_exists($field, $data)) {
                if ($fillMissing) {
                    $data[$field] = Health::LOOKUP_NOT_APPLICABLE;
                }

                continue;
            }

            if ($data[$field] === null || $data[$field] === '') {
                $data[$field] = Health::LOOKUP_NOT_APPLICABLE;
            }
        }

        return $data;
    }

    /**
     * PWD ID is optional. Empty values are stored as 0 because the column
     * is INT NOT NULL.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyOptionalPwdId(array $data, bool $fillMissing = false): array
    {
        if (! array_key_exists('pwd_id_number', $data)) {
            if ($fillMissing) {
                $data['pwd_id_number'] = Health::PWD_ID_NOT_APPLICABLE;
            }

            return $data;
        }

        if ($data['pwd_id_number'] === null || $data['pwd_id_number'] === '') {
            $data['pwd_id_number'] = Health::PWD_ID_NOT_APPLICABLE;
        }

        return $data;
    }

    private function nullableLookupId(mixed $id): ?int
    {
        $value = (int) $id;

        return $value === Health::LOOKUP_NOT_APPLICABLE ? null : $value;
    }

    /**
     * health.disability_id is a required FK. Accept either a selected
     * disability_id or a typed label (reuse existing, otherwise create).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function mapDisabilityToId(array $data, bool $required = false): array
    {
        if (array_key_exists('disability_id', $data) && (int) $data['disability_id'] > 0) {
            unset($data['disability']);

            return $data;
        }

        if (! $required && ! array_key_exists('disability', $data)) {
            unset($data['disability']);

            return $data;
        }

        $label = is_string($data['disability'] ?? null)
            ? trim($data['disability'])
            : '';

        if ($label === '') {
            throw ValidationException::withMessages([
                'disability' => ['Disability is required.'],
            ]);
        }

        $data['disability_id'] = Disability::findOrCreateByLabel($label)->disability_id;
        unset($data['disability']);

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(Health $health): array
    {
        $health->loadMissing([
            'healthInsurance',
            'facilityVisitedPast12Mos',
            'facilityVisitReason',
            'disabilityType',
        ]);

        return [
            'health insurance' => $health->health_insurance_id === Health::LOOKUP_NOT_APPLICABLE
                ? 'N/A'
                : (string) ($health->healthInsurance?->health_insurance ?? $health->health_insurance_id),
            'facility visited' => $health->facility_visited_past_12mos_id === Health::LOOKUP_NOT_APPLICABLE
                ? 'N/A'
                : (string) ($health->facilityVisitedPast12Mos?->facility_visited_past_12mos ?? $health->facility_visited_past_12mos_id),
            'visit reason' => $health->facility_visit_reason_id === Health::LOOKUP_NOT_APPLICABLE
                ? 'N/A'
                : (string) ($health->facilityVisitReason?->facility_visit_reason ?? $health->facility_visit_reason_id),
            'disability' => (string) ($health->disabilityType?->disability ?? ''),
            'pwd id number' => $health->pwd_id_number === Health::PWD_ID_NOT_APPLICABLE
                ? 'N/A'
                : (string) $health->pwd_id_number,
        ];
    }
}
