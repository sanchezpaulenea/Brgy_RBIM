<?php

namespace App\Services\ResidentManagement\Health;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
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
        $data = $this->applyPwdIdNumber($data);

        return DB::transaction(function () use ($performedBy, $data) {
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
        $data = $this->applyPwdIdNumber($data, $health);

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
        ]);

        return [
            'health_id' => $health->health_id,
            'resident_id' => $health->resident_id,
            'health_insurance_id' => $health->health_insurance_id,
            'health_insurance' => $health->healthInsurance?->health_insurance,
            'facility_visited_past_12mos_id' => $health->facility_visited_past_12mos_id,
            'facility_visited_past_12mos' => $health->facilityVisitedPast12Mos?->facility_visited_past_12mos,
            'facility_visit_reason_id' => $health->facility_visit_reason_id,
            'facility_visit_reason' => $health->facilityVisitReason?->facility_visit_reason,
            'disability' => $health->disability,
            'pwd_id_number' => $health->pwd_id_number === Health::PWD_ID_NOT_APPLICABLE
                ? null
                : $health->pwd_id_number,
        ];
    }

    /**
     * health.pwd_id_number is INT NOT NULL with no DEFAULT. A real PWD ID is
     * only collected when the resident has a disability. When disability is
     * empty or "None", 0 is stored as "not applicable / no PWD ID" because
     * NULL is not allowed without a schema change.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyPwdIdNumber(array $data, ?Health $existing = null): array
    {
        $disability = array_key_exists('disability', $data)
            ? $data['disability']
            : $existing?->disability;

        if (! Health::indicatesDisability($disability)) {
            $data['pwd_id_number'] = Health::PWD_ID_NOT_APPLICABLE;
        }

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
        ]);

        return [
            'health insurance' => (string) ($health->healthInsurance?->health_insurance ?? $health->health_insurance_id),
            'facility visited' => (string) ($health->facilityVisitedPast12Mos?->facility_visited_past_12mos ?? $health->facility_visited_past_12mos_id),
            'visit reason' => (string) ($health->facilityVisitReason?->facility_visit_reason ?? $health->facility_visit_reason_id),
            'disability' => (string) ($health->disability ?? ''),
            'pwd id number' => $health->pwd_id_number === Health::PWD_ID_NOT_APPLICABLE
                ? 'N/A'
                : (string) $health->pwd_id_number,
        ];
    }
}
