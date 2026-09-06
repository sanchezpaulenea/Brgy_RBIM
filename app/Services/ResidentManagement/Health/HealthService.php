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
        ];
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
        ];
    }
}
