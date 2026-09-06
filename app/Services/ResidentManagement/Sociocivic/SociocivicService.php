<?php

namespace App\Services\ResidentManagement\Sociocivic;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Sociocivic\Sociocivic;
use App\Models\ResidentManagement\Sociocivic\SoloParentStatus;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Sociocivic\SociocivicRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SociocivicService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected SociocivicRepositoryInterface $sociocivicRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Resident $resident, array $data): array
    {
        if ($this->sociocivicRepository->findByResidentId($resident->resident_id) !== null) {
            throw ValidationException::withMessages([
                'sociocivic' => ['A sociocivic record already exists for this resident.'],
            ]);
        }

        $data['resident_id'] = $resident->resident_id;
        $data = $this->applyAgeThresholds($resident, $data);

        return DB::transaction(function () use ($performedBy, $data) {
            $sociocivic = $this->sociocivicRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $sociocivic->sociocivic_id,
                description: 'Create sociocivic',
                oldValue: null,
                newValue: (string) ($sociocivic->soloParentStatus?->solo_parent_status ?? $sociocivic->solo_parent_status_id),
                target: 'record',
                entity: 'sociocivic',
            );

            return $this->formatRecord($sociocivic);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, Sociocivic $sociocivic, array $data): array
    {
        $sociocivic->loadMissing('resident');
        $previous = $this->auditSnapshot($sociocivic);
        $data = $this->applyAgeThresholds($sociocivic->resident, array_merge($sociocivic->only([
            'solo_parent_status_id',
            'registered_sen_citizen',
            'registered_barangay_voter',
        ]), $data));

        return DB::transaction(function () use ($performedBy, $sociocivic, $data, $previous) {
            $updated = $this->sociocivicRepository->update($sociocivic, $data);

            $this->logFieldChanges(
                $performedBy,
                $updated->sociocivic_id,
                'sociocivic',
                $previous,
                $this->auditSnapshot($updated),
                'Updated sociocivic',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Sociocivic $sociocivic): array
    {
        $sociocivic->loadMissing(['soloParentStatus', 'resident']);
        $relevance = $sociocivic->resident?->sociocivicFieldRelevance() ?? [
            'solo_parent' => false,
            'senior_citizen' => false,
            'barangay_voter' => false,
        ];

        return [
            'sociocivic_id' => $sociocivic->sociocivic_id,
            'resident_id' => $sociocivic->resident_id,
            'solo_parent_status_id' => $sociocivic->solo_parent_status_id,
            'solo_parent_status' => $sociocivic->soloParentStatus?->solo_parent_status,
            'registered_sen_citizen' => (bool) $sociocivic->registered_sen_citizen,
            'registered_barangay_voter' => $sociocivic->registered_barangay_voter,
            'field_relevance' => $relevance,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyAgeThresholds(?Resident $resident, array $data): array
    {
        $relevance = $resident?->sociocivicFieldRelevance() ?? [
            'solo_parent' => false,
            'senior_citizen' => false,
            'barangay_voter' => false,
        ];

        if (! $relevance['solo_parent']) {
            $data['solo_parent_status_id'] = SoloParentStatus::NON_SOLO_PARENT;
        }

        if (! $relevance['senior_citizen']) {
            $data['registered_sen_citizen'] = false;
        }

        if (! $relevance['barangay_voter']) {
            $data['registered_barangay_voter'] = null;
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(Sociocivic $sociocivic): array
    {
        $sociocivic->loadMissing('soloParentStatus');

        return [
            'solo parent status' => (string) ($sociocivic->soloParentStatus?->solo_parent_status ?? $sociocivic->solo_parent_status_id),
            'registered senior citizen' => $sociocivic->registered_sen_citizen ? 'Yes' : 'No',
            'registered barangay voter' => (string) ($sociocivic->registered_barangay_voter ?? ''),
        ];
    }
}
