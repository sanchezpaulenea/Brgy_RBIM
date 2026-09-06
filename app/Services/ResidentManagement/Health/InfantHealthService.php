<?php

namespace App\Services\ResidentManagement\Health;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Health\InfantHealth;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\InfantHealthRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InfantHealthService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected InfantHealthRepositoryInterface $infantHealthRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Resident $resident, array $data): array
    {
        $this->assertInfant($resident);

        if ($this->infantHealthRepository->findByResidentId($resident->resident_id) !== null) {
            throw ValidationException::withMessages([
                'infant_health' => ['An infant health record already exists for this resident.'],
            ]);
        }

        $data['resident_id'] = $resident->resident_id;

        return DB::transaction(function () use ($performedBy, $data) {
            $infantHealth = $this->infantHealthRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $infantHealth->infant_health_id,
                description: 'Create infant health',
                oldValue: null,
                newValue: (string) $infantHealth->immunization,
                target: 'record',
                entity: 'infant_health',
            );

            return $this->formatRecord($infantHealth);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, InfantHealth $infantHealth, array $data): array
    {
        $infantHealth->loadMissing('resident');
        $this->assertInfant($infantHealth->resident);

        $previous = $this->auditSnapshot($infantHealth);

        return DB::transaction(function () use ($performedBy, $infantHealth, $data, $previous) {
            $updated = $this->infantHealthRepository->update($infantHealth, $data);

            $this->logFieldChanges(
                $performedBy,
                $updated->infant_health_id,
                'infant_health',
                $previous,
                $this->auditSnapshot($updated),
                'Updated infant health',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(InfantHealth $infantHealth): array
    {
        $infantHealth->loadMissing(['placeOfDelivery', 'birthAttendant']);

        return [
            'infant_health_id' => $infantHealth->infant_health_id,
            'resident_id' => $infantHealth->resident_id,
            'place_of_delivery_id' => $infantHealth->place_of_delivery_id,
            'place_of_delivery' => $infantHealth->placeOfDelivery?->place_of_delivery,
            'birth_attendant_id' => $infantHealth->birth_attendant_id,
            'birth_attendant' => $infantHealth->birthAttendant?->birth_attendant,
            'immunization' => $infantHealth->immunization,
        ];
    }

    private function assertInfant(?Resident $resident): void
    {
        if ($resident === null || ! $resident->canHaveInfantHealth()) {
            throw ValidationException::withMessages([
                'resident_id' => ['Infant health applies only to residents aged 0 to 11 months.'],
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(InfantHealth $infantHealth): array
    {
        $infantHealth->loadMissing(['placeOfDelivery', 'birthAttendant']);

        return [
            'place of delivery' => (string) ($infantHealth->placeOfDelivery?->place_of_delivery ?? $infantHealth->place_of_delivery_id),
            'birth attendant' => (string) ($infantHealth->birthAttendant?->birth_attendant ?? $infantHealth->birth_attendant_id),
            'immunization' => (string) $infantHealth->immunization,
        ];
    }
}
