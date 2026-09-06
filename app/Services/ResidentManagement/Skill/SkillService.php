<?php

namespace App\Services\ResidentManagement\Skill;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Skill\SkillsDevelopment;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Skill\SkillRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SkillService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected SkillRepositoryInterface $skillRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Resident $resident, array $data): array
    {
        $this->assertEligible($resident);

        if ($this->skillRepository->findByResidentId($resident->resident_id) !== null) {
            throw ValidationException::withMessages([
                'skills' => ['A skills development record already exists for this resident.'],
            ]);
        }

        $data['resident_id'] = $resident->resident_id;

        return DB::transaction(function () use ($performedBy, $data) {
            $skillsDevelopment = $this->skillRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $skillsDevelopment->skills_development_id,
                description: 'Create skills development',
                oldValue: null,
                newValue: (string) $skillsDevelopment->skills_development_training,
                target: 'record',
                entity: 'skills_development',
            );

            return $this->formatRecord($skillsDevelopment);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, SkillsDevelopment $skillsDevelopment, array $data): array
    {
        $skillsDevelopment->loadMissing('resident');
        $this->assertEligible($skillsDevelopment->resident);

        $previous = $this->auditSnapshot($skillsDevelopment);

        return DB::transaction(function () use ($performedBy, $skillsDevelopment, $data, $previous) {
            $updated = $this->skillRepository->update($skillsDevelopment, $data);

            $this->logFieldChanges(
                $performedBy,
                $updated->skills_development_id,
                'skills_development',
                $previous,
                $this->auditSnapshot($updated),
                'Updated skills development',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(SkillsDevelopment $skillsDevelopment): array
    {
        $skillsDevelopment->loadMissing('skillType');

        return [
            'skills_development_id' => $skillsDevelopment->skills_development_id,
            'resident_id' => $skillsDevelopment->resident_id,
            'skills_development_training' => $skillsDevelopment->skills_development_training,
            'skill_type_id' => $skillsDevelopment->skill_type_id,
            'skill_type' => $skillsDevelopment->skillType?->skill_type,
        ];
    }

    private function assertEligible(?Resident $resident): void
    {
        if ($resident === null || ! $resident->canHaveSkills()) {
            throw ValidationException::withMessages([
                'resident_id' => ['Skills development applies only to residents aged 15 and above.'],
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(SkillsDevelopment $skillsDevelopment): array
    {
        $skillsDevelopment->loadMissing('skillType');

        return [
            'training' => (string) $skillsDevelopment->skills_development_training,
            'skill type' => (string) ($skillsDevelopment->skillType?->skill_type ?? $skillsDevelopment->skill_type_id),
        ];
    }
}
