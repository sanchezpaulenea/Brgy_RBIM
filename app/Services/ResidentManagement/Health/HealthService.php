<?php

namespace App\Services\ResidentManagement\Health;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Health\Disability;
use App\Models\ResidentManagement\Health\FacilityVisitedPast12Mos;
use App\Models\ResidentManagement\Health\FacilityVisitReason;
use App\Models\ResidentManagement\Health\Health;
use App\Models\ResidentManagement\Health\HealthInsurance;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\HealthRepositoryInterface;
use App\Rules\ValidPwdIdNumber;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use App\Services\ResidentManagement\Concerns\SerializesResidentSectionWrites;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HealthService
{
    use LogsAuditableFieldChanges;
    use SerializesResidentSectionWrites;

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
        $data['resident_id'] = $resident->resident_id;
        $data = $this->applyHealthRules($data, fillMissing: true);

        return $this->withResidentLock($resident->resident_id, function () use ($performedBy, $resident, $data) {
            if ($this->healthRepository->findByResidentId($resident->resident_id) !== null) {
                throw ValidationException::withMessages([
                    'health' => ['A health record already exists for this resident.'],
                ]);
            }

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
        $data = $this->applyHealthRules(array_merge($health->only([
            'health_insurance_id',
            'facility_visited_past_12mos_id',
            'facility_visit_reason_id',
            'disability_id',
            'pwd_id_number',
        ]), $data));

        return DB::transaction(function () use ($performedBy, $health, $data, $previous) {
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

        $facilityIsNone = $health->facilityVisitedPast12Mos?->indicatesNone() ?? false;

        return [
            'health_id' => $health->health_id,
            'resident_id' => $health->resident_id,
            'health_insurance_id' => $health->health_insurance_id,
            'health_insurance' => $health->healthInsurance?->health_insurance,
            'facility_visited_past_12mos_id' => $health->facility_visited_past_12mos_id,
            'facility_visited_past_12mos' => $health->facilityVisitedPast12Mos?->facility_visited_past_12mos,
            'facility_visit_reason_id' => $facilityIsNone ? null : $health->facility_visit_reason_id,
            'facility_visit_reason' => $facilityIsNone ? null : $health->facilityVisitReason?->facility_visit_reason,
            'disability_id' => $health->disability_id,
            'disability' => $health->disabilityType?->disability,
            'pwd_id_number' => Health::indicatesDisability($health->disabilityType?->disability)
                ? $health->pwd_id_number
                : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyHealthRules(array $data, bool $fillMissing = false): array
    {
        $data = $this->mapLabeledLookup($data, 'health_insurance_id', 'health_insurance', HealthInsurance::class, $fillMissing);
        $data = $this->mapLabeledLookup($data, 'facility_visited_past_12mos_id', 'facility_visited_past_12mos', FacilityVisitedPast12Mos::class, $fillMissing);
        $data = $this->mapLabeledLookup($data, 'facility_visit_reason_id', 'facility_visit_reason', FacilityVisitReason::class, false);
        $data = $this->mapDisabilityToId($data, required: $fillMissing);
        $data = $this->applyFacilityVisitReason($data);
        $data = $this->applyOptionalPwdId($data, $fillMissing);

        if (! Health::indicatesDisability(
            Disability::query()->find((int) ($data['disability_id'] ?? 0))?->disability
        )) {
            $data['pwd_id_number'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  class-string<HealthInsurance|FacilityVisitedPast12Mos|FacilityVisitReason>  $modelClass
     * @return array<string, mixed>
     */
    private function mapLabeledLookup(
        array $data,
        string $idField,
        string $labelField,
        string $modelClass,
        bool $required,
    ): array {
        if (array_key_exists($idField, $data) && (int) $data[$idField] > 0) {
            unset($data[$labelField]);

            return $data;
        }

        $label = is_string($data[$labelField] ?? null) ? trim($data[$labelField]) : '';

        if ($label !== '') {
            $data[$idField] = $modelClass::findOrCreateByLabel($label)->getKey();
            unset($data[$labelField]);

            return $data;
        }

        unset($data[$labelField]);

        if ($required) {
            throw ValidationException::withMessages([
                $idField => [str_replace('_', ' ', $labelField).' is required.'],
            ]);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyFacilityVisitReason(array $data): array
    {
        $facility = FacilityVisitedPast12Mos::query()
            ->find((int) ($data['facility_visited_past_12mos_id'] ?? 0));

        if ($facility?->indicatesNone()) {
            $notApplicableId = FacilityVisitReason::notApplicableId();

            if ($notApplicableId === null) {
                throw ValidationException::withMessages([
                    'facility_visit_reason_id' => ['The Not Applicable facility visit reason lookup is missing.'],
                ]);
            }

            $data['facility_visit_reason_id'] = $notApplicableId;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyOptionalPwdId(array $data, bool $fillMissing = false): array
    {
        if (! array_key_exists('pwd_id_number', $data)) {
            if ($fillMissing) {
                $data['pwd_id_number'] = null;
            }

            return $data;
        }

        $data['pwd_id_number'] = ValidPwdIdNumber::normalize($data['pwd_id_number']);

        return $data;
    }

    /**
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
            'health insurance' => (string) ($health->healthInsurance?->health_insurance ?? $health->health_insurance_id),
            'facility visited' => (string) ($health->facilityVisitedPast12Mos?->facility_visited_past_12mos ?? $health->facility_visited_past_12mos_id),
            'visit reason' => $health->facilityVisitedPast12Mos?->indicatesNone()
                ? 'N/A'
                : (string) ($health->facilityVisitReason?->facility_visit_reason ?? $health->facility_visit_reason_id),
            'disability' => (string) ($health->disabilityType?->disability ?? ''),
            'pwd id number' => (string) ($health->pwd_id_number ?? 'N/A'),
        ];
    }
}
