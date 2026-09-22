<?php

namespace App\Services\ResidentManagement\Health;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Health\FamilyPlanningMethod;
use App\Models\ResidentManagement\Health\SourceOfFPMethod;
use App\Models\ResidentManagement\Health\WomenHealth;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\HealthRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\WomenHealthRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use App\Services\ResidentManagement\Concerns\SerializesResidentSectionWrites;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WomenHealthService
{
    use LogsAuditableFieldChanges;
    use SerializesResidentSectionWrites;

    public function __construct(
        protected WomenHealthRepositoryInterface $womenHealthRepository,
        protected HealthRepositoryInterface $healthRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Resident $resident, array $data): array
    {
        $this->assertEligible($resident);

        $data = $this->applyFamilyPlanningRules($data);

        return $this->withResidentLock($resident->resident_id, function () use ($performedBy, $resident, $data) {
            $health = $this->healthRepository->findByResidentId($resident->resident_id);

            if ($health === null) {
                throw ValidationException::withMessages([
                    'health_id' => ['A health record must exist before women\'s health can be recorded.'],
                ]);
            }

            if ($this->womenHealthRepository->findByHealthId($health->health_id) !== null) {
                throw ValidationException::withMessages([
                    'women_health' => ['A women\'s health record already exists for this resident.'],
                ]);
            }

            $data['health_id'] = $health->health_id;

            $womenHealth = $this->womenHealthRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $womenHealth->women_health_id,
                description: 'Create women health',
                oldValue: null,
                newValue: (string) $womenHealth->living_children,
                target: 'record',
                entity: 'women_health',
            );

            return $this->formatRecord($womenHealth);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, WomenHealth $womenHealth, array $data): array
    {
        $womenHealth->loadMissing('health.resident');
        $this->assertEligible($womenHealth->health?->resident);

        $previous = $this->auditSnapshot($womenHealth);
        $data = $this->applyFamilyPlanningRules(array_merge($womenHealth->only([
            'number_pregnancies',
            'living_children',
            'family_planning_method_id',
            'source_of_fp_method_id',
            'have_intention_to_use_fp',
            'health_id',
        ]), $data));

        return DB::transaction(function () use ($performedBy, $womenHealth, $data, $previous) {
            $updated = $this->womenHealthRepository->update($womenHealth, $data);

            $this->logFieldChanges(
                $performedBy,
                $updated->women_health_id,
                'women_health',
                $previous,
                $this->auditSnapshot($updated),
                'Updated women health',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(WomenHealth $womenHealth): array
    {
        $womenHealth->loadMissing(['familyPlanningMethod', 'sourceOfFpMethod', 'health']);

        return [
            'women_health_id' => $womenHealth->women_health_id,
            'health_id' => $womenHealth->health_id,
            'resident_id' => $womenHealth->health?->resident_id,
            'number_pregnancies' => $womenHealth->number_pregnancies,
            'living_children' => $womenHealth->living_children,
            'family_planning_method_id' => $womenHealth->family_planning_method_id,
            'family_planning_method' => $womenHealth->familyPlanningMethod?->family_planning_method,
            'source_of_fp_method_id' => $womenHealth->familyPlanningMethod?->indicatesNone()
                ? null
                : $womenHealth->source_of_fp_method_id,
            'source_of_fp_method' => $womenHealth->familyPlanningMethod?->indicatesNone()
                ? null
                : $womenHealth->sourceOfFpMethod?->source_of_fp_method,
            'have_intention_to_use_fp' => $womenHealth->familyPlanningMethod?->indicatesNone()
                ? null
                : (bool) $womenHealth->have_intention_to_use_fp,
        ];
    }

    /**
     * "None" skips Q24–Q25. source_of_fp_method_id is INT NOT NULL, so the
     * existing "Not Applicable" lookup row is stored.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyFamilyPlanningRules(array $data): array
    {
        $methodId = (int) ($data['family_planning_method_id'] ?? 0);
        $method = $methodId > 0
            ? FamilyPlanningMethod::query()->where('family_planning_method_id', $methodId)->first()
            : null;

        if ($method === null) {
            throw ValidationException::withMessages([
                'family_planning_method_id' => ['Family planning method is required.'],
            ]);
        }

        if ($method->indicatesNone()) {
            $notApplicableId = SourceOfFPMethod::notApplicableId();

            if ($notApplicableId === null) {
                throw ValidationException::withMessages([
                    'source_of_fp_method_id' => ['The Not Applicable source of family planning method lookup is missing.'],
                ]);
            }

            $data['source_of_fp_method_id'] = $notApplicableId;
            $data['have_intention_to_use_fp'] = false;

            return $data;
        }

        if (! array_key_exists('have_intention_to_use_fp', $data)
            || $data['have_intention_to_use_fp'] === null
            || $data['have_intention_to_use_fp'] === ''
        ) {
            $data['have_intention_to_use_fp'] = false;
        }

        return $data;
    }

    private function assertEligible(?Resident $resident): void
    {
        if ($resident === null || ! $resident->canHaveWomenHealth()) {
            throw ValidationException::withMessages([
                'resident_id' => ['Women\'s health applies only to female residents aged 10 to 54.'],
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(WomenHealth $womenHealth): array
    {
        $womenHealth->loadMissing(['familyPlanningMethod', 'sourceOfFpMethod']);

        return [
            'number of pregnancies' => (string) $womenHealth->number_pregnancies,
            'living children' => (string) $womenHealth->living_children,
            'family planning method' => (string) ($womenHealth->familyPlanningMethod?->family_planning_method ?? $womenHealth->family_planning_method_id),
            'source of fp method' => $womenHealth->familyPlanningMethod?->indicatesNone()
                ? 'N/A'
                : (string) ($womenHealth->sourceOfFpMethod?->source_of_fp_method ?? $womenHealth->source_of_fp_method_id),
            'intention to use fp' => $womenHealth->familyPlanningMethod?->indicatesNone()
                ? 'N/A'
                : ($womenHealth->have_intention_to_use_fp ? 'Yes' : 'No'),
        ];
    }
}
