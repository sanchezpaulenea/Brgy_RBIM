<?php

namespace App\Services\ResidentManagement\Education;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Education\CurrentEnrollmentStatus;
use App\Models\ResidentManagement\Education\Education;
use App\Models\ResidentManagement\Education\HighestLvlOfEduc;
use App\Models\ResidentManagement\Education\SchoolLvl;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Education\EducationRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EducationService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected EducationRepositoryInterface $educationRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getForResident(Resident $resident): array
    {
        $education = $this->educationRepository->findByResidentId($resident->resident_id);

        return $education === null ? [] : $this->formatRecord($education);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(User $performedBy, Resident $resident, array $data): array
    {
        if ($this->educationRepository->findByResidentId($resident->resident_id) !== null) {
            throw ValidationException::withMessages([
                'education' => ['An education record already exists for this resident.'],
            ]);
        }

        $data['resident_id'] = $resident->resident_id;
        $data = $this->applyAgeThresholds($resident, $data);
        $data = $this->applyEnrollmentRules($data);

        return DB::transaction(function () use ($performedBy, $data) {
            $education = $this->educationRepository->create($data);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $education->education_id,
                description: 'Create education',
                oldValue: null,
                newValue: (string) ($education->highestLvlOfEduc?->lvl_of_educ ?? $education->highest_lvl_of_educ_id),
                target: 'record',
                entity: 'education',
            );

            return $this->formatRecord($education);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, Education $education, array $data): array
    {
        $education->loadMissing('resident');
        $previous = $this->auditSnapshot($education);
        $data = $this->applyAgeThresholds($education->resident, array_merge($education->only([
            'highest_lvl_of_educ_id',
            'current_enrollement_status_id',
            'school_lvl_id',
            'place_of_school_brgy',
            'place_of_school_city_municipality',
        ]), $data));
        $data = $this->applyEnrollmentRules($data);

        return DB::transaction(function () use ($performedBy, $education, $data, $previous) {
            $updated = $this->educationRepository->update($education, $data);

            $this->logFieldChanges(
                $performedBy,
                $updated->education_id,
                'education',
                $previous,
                $this->auditSnapshot($updated),
                'Updated education',
            );

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Education $education): array
    {
        $education->loadMissing(array_merge($this->relations(), ['resident']));
        $relevance = $education->resident?->educationFieldRelevance() ?? [
            'highest_level' => false,
            'enrollment' => false,
        ];

        return [
            'education_id' => $education->education_id,
            'resident_id' => $education->resident_id,
            'highest_lvl_of_educ_id' => $education->highest_lvl_of_educ_id,
            'highest_lvl_of_educ' => $education->highestLvlOfEduc?->lvl_of_educ,
            'current_enrollement_status_id' => $education->current_enrollement_status_id,
            'current_enrollment_status' => $education->currentEnrollmentStatus?->current_enrollement_status,
            'school_lvl_id' => $education->school_lvl_id,
            'school_lvl' => $education->schoolLvl?->school_lvl,
            'place_of_school_brgy' => $education->place_of_school_brgy,
            'place_of_school_city_municipality' => $education->place_of_school_city_municipality,
            'field_relevance' => $relevance,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyAgeThresholds(?Resident $resident, array $data): array
    {
        $relevance = $resident?->educationFieldRelevance() ?? [
            'highest_level' => false,
            'enrollment' => false,
        ];

        if (! $relevance['highest_level']) {
            $highestId = HighestLvlOfEduc::notApplicableId();

            if ($highestId === null) {
                throw ValidationException::withMessages([
                    'highest_lvl_of_educ_id' => ['The Not Applicable highest level of education lookup is missing.'],
                ]);
            }

            $data['highest_lvl_of_educ_id'] = $highestId;
        }

        if (! $relevance['enrollment']) {
            $data['current_enrollement_status_id'] = CurrentEnrollmentStatus::NOT_ENROLLED;
            $data['place_of_school_brgy'] = null;
            $data['place_of_school_city_municipality'] = null;
            $data['school_lvl_id'] = $this->notApplicableSchoolLvlId();
        }

        return $data;
    }

    private function notApplicableSchoolLvlId(): int
    {
        $id = SchoolLvl::notApplicableId();

        if ($id !== null) {
            return $id;
        }

        return (int) SchoolLvl::query()->create([
            'school_lvl' => SchoolLvl::NOT_APPLICABLE,
        ])->school_lvl_id;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyEnrollmentRules(array $data): array
    {
        $statusId = (int) ($data['current_enrollement_status_id'] ?? 0);

        if ($statusId === 0) {
            return $data;
        }

        $status = CurrentEnrollmentStatus::query()
            ->where('current_enrollment_status_id', $statusId)
            ->first();

        if ($status === null || ! $status->isNotEnrolled()) {
            return $data;
        }

        $data['place_of_school_brgy'] = null;
        $data['place_of_school_city_municipality'] = null;

        $notApplicableId = SchoolLvl::notApplicableId();

        if ($notApplicableId === null) {
            throw ValidationException::withMessages([
                'school_lvl_id' => ['The Not Applicable school level lookup is missing.'],
            ]);
        }

        $data['school_lvl_id'] = $notApplicableId;

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(Education $education): array
    {
        $education->loadMissing($this->relations());

        return [
            'highest level of education' => (string) ($education->highestLvlOfEduc?->lvl_of_educ ?? $education->highest_lvl_of_educ_id),
            'enrollment status' => (string) ($education->currentEnrollmentStatus?->current_enrollement_status ?? $education->current_enrollement_status_id),
            'school level' => (string) ($education->schoolLvl?->school_lvl ?? $education->school_lvl_id),
            'school barangay' => (string) ($education->place_of_school_brgy ?? ''),
            'school city' => (string) ($education->place_of_school_city_municipality ?? ''),
        ];
    }

    /**
     * @return list<string>
     */
    private function relations(): array
    {
        return [
            'highestLvlOfEduc',
            'currentEnrollmentStatus',
            'schoolLvl',
        ];
    }
}
