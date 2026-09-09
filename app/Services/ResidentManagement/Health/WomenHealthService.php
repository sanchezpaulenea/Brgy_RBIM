<?php

namespace App\Services\ResidentManagement\Health;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Health\FamilyPlanningMethod;
use App\Models\ResidentManagement\Health\WomenHealth;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\HealthRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Health\WomenHealthRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WomenHealthService
{
    use LogsAuditableFieldChanges;

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
        $data = $this->applyFamilyPlanningRules($data);

        return DB::transaction(function () use ($performedBy, $data) {
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
            'family_planning_method_id' => $this->nullableLookupId($womenHealth->family_planning_method_id),
            'family_planning_method' => $womenHealth->family_planning_method_id === WomenHealth::LOOKUP_NOT_APPLICABLE
                ? null
                : $womenHealth->familyPlanningMethod?->family_planning_method,
            'source_of_fp_method_id' => $this->nullableLookupId($womenHealth->source_of_fp_method_id),
            'source_of_fp_method' => $womenHealth->source_of_fp_method_id === WomenHealth::LOOKUP_NOT_APPLICABLE
                ? null
                : $womenHealth->sourceOfFpMethod?->source_of_fp_method,
            'have_intention_to_use_fp' => $womenHealth->family_planning_method_id === WomenHealth::LOOKUP_NOT_APPLICABLE
                || ($womenHealth->familyPlanningMethod?->indicatesNone() ?? false)
                ? null
                : (bool) $womenHealth->have_intention_to_use_fp,
        ];
    }

    /**
     * Q23 is optional. Empty or "None" skips Q24–Q25. INT NOT NULL lookup
     * columns store 0; intention stores false.
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

        if ($methodId === 0 || $method?->indicatesNone()) {
            $data['family_planning_method_id'] = WomenHealth::LOOKUP_NOT_APPLICABLE;
            $data['source_of_fp_method_id'] = WomenHealth::LOOKUP_NOT_APPLICABLE;
            $data['have_intention_to_use_fp'] = false;

            return $data;
        }

        if (! array_key_exists('source_of_fp_method_id', $data)
            || $data['source_of_fp_method_id'] === null
            || $data['source_of_fp_method_id'] === ''
        ) {
            $data['source_of_fp_method_id'] = WomenHealth::LOOKUP_NOT_APPLICABLE;
        }

        if (! array_key_exists('have_intention_to_use_fp', $data)
            || $data['have_intention_to_use_fp'] === null
            || $data['have_intention_to_use_fp'] === ''
        ) {
            $data['have_intention_to_use_fp'] = false;
        }

        return $data;
    }

    private function nullableLookupId(mixed $id): ?int
    {
        $value = (int) $id;

        return $value === WomenHealth::LOOKUP_NOT_APPLICABLE ? null : $value;
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
            'family planning method' => $womenHealth->family_planning_method_id === WomenHealth::LOOKUP_NOT_APPLICABLE
                ? 'N/A'
                : (string) ($womenHealth->familyPlanningMethod?->family_planning_method ?? $womenHealth->family_planning_method_id),
            'source of fp method' => $womenHealth->source_of_fp_method_id === WomenHealth::LOOKUP_NOT_APPLICABLE
                ? 'N/A'
                : (string) ($womenHealth->sourceOfFpMethod?->source_of_fp_method ?? $womenHealth->source_of_fp_method_id),
            'intention to use fp' => $womenHealth->family_planning_method_id === WomenHealth::LOOKUP_NOT_APPLICABLE
                || ($womenHealth->familyPlanningMethod?->indicatesNone() ?? false)
                ? 'N/A'
                : ($womenHealth->have_intention_to_use_fp ? 'Yes' : 'No'),
        ];
    }
}
