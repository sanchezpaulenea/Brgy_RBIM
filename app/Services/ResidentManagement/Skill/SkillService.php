<?php

namespace App\Services\ResidentManagement\Skill;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Skill\SkillsDevelopment;
use App\Models\ResidentManagement\Skill\SkillsDevelopmentTraining;
use App\Models\ResidentManagement\Skill\SkillType;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Skill\SkillRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use App\Services\ResidentManagement\Concerns\SerializesResidentSectionWrites;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SkillService
{
    use LogsAuditableFieldChanges;
    use SerializesResidentSectionWrites;

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

        $data = $this->resolveTraining($data, true);
        $data = $this->resolveSkillType($data);
        $data['resident_id'] = $resident->resident_id;

        return $this->withResidentLock($resident->resident_id, function () use ($performedBy, $resident, $data) {
            if ($this->skillRepository->findByResidentId($resident->resident_id) !== null) {
                throw ValidationException::withMessages([
                    'skills' => ['A skills development record already exists for this resident.'],
                ]);
            }

            $skillsDevelopment = $this->skillRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $skillsDevelopment->skills_development_id,
                description: 'Create skills development',
                oldValue: null,
                newValue: (string) ($skillsDevelopment->training?->skills_development_training ?? ''),
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

        $data = $this->resolveTraining($data, false);
        $data = $this->resolveSkillType($data);
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

    private function resolveTraining(array $data, bool $required): array
    {
        if ((int) ($data['skills_development_training_id'] ?? 0) > 0) {
            unset($data['skills_development_training']);

            return $data;
        }

        $label = is_string($data['skills_development_training'] ?? null)
            ? trim($data['skills_development_training'])
            : '';

        if ($label === '') {
            unset($data['skills_development_training']);

            if ($required) {
                throw ValidationException::withMessages([
                    'skills_development_training' => ['Skills development training is required.'],
                ]);
            }

            return $data;
        }

        $data['skills_development_training_id'] = SkillsDevelopmentTraining::findOrCreateByLabel($label)
            ->skills_development_training_id;
        unset($data['skills_development_training']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function resolveSkillType(array $data): array
    {
        if ((int) ($data['skill_type_id'] ?? 0) > 0) {
            unset($data['skill_type']);

            return $data;
        }

        $label = is_string($data['skill_type'] ?? null) ? trim($data['skill_type']) : '';

        if ($label === '') {
            unset($data['skill_type']);

            return $data;
        }

        $data['skill_type_id'] = SkillType::findOrCreateByLabel($label)->skill_type_id;
        unset($data['skill_type']);

        return $data;
    }

    public function formatRecord(SkillsDevelopment $skillsDevelopment): array
    {
        $skillsDevelopment->loadMissing(['skillType', 'training']);

        return [
            'skills_development_id' => $skillsDevelopment->skills_development_id,
            'resident_id' => $skillsDevelopment->resident_id,
            'skills_development_training_id' => $skillsDevelopment->skills_development_training_id,
            'skills_development_training' => $skillsDevelopment->training?->skills_development_training,
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
        $skillsDevelopment->loadMissing(['skillType', 'training']);

        return [
            'training' => (string) ($skillsDevelopment->training?->skills_development_training ?? ''),
            'skill type' => (string) ($skillsDevelopment->skillType?->skill_type ?? $skillsDevelopment->skill_type_id),
        ];
    }
}
