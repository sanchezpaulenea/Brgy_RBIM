<?php

namespace App\Services\HouseholdManagement;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelStatus;
use App\Models\HouseholdManagement\CensusStatus;
use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\HouseholdAssessment;
use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\BarangayPersonnel\BarangayPersonnelRepositoryInterface;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdAssessmentRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class HouseholdAssessmentServices
{
    public function __construct(
        protected HouseholdAssessmentRepositoryInterface $householdAssessmentRepository,
        protected BarangayPersonnelRepositoryInterface $personnelRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * Latest assessment per household (highest assessment_id). Visit history stays on the household detail list.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listAllAssessments(): array
    {
        return $this->householdAssessmentRepository
            ->listAll()
            ->unique('household_id')
            ->values()
            ->map(function (HouseholdAssessment $assessment) {
                $payload = $this->formatListRecord($assessment);
                $payload['is_latest'] = true;

                return $payload;
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAssessments(Household $household): array
    {
        return $this->householdAssessmentRepository
            ->listByHousehold($household->household_id)
            ->values()
            ->map(function (HouseholdAssessment $assessment, int $index) {
                $payload = $this->formatAssessment($assessment) ?? [];
                $payload['is_latest'] = $index === 0;

                return $payload;
            })
            ->all();
    }

    /**
     * @return array<int, array{id: int, personnel_id: int, label: string}>
     */
    public function listPersonnelOptions(): array
    {
        return $this->personnelRepository
            ->all()
            ->filter(fn (BarangayPersonnel $personnel) => $personnel->personnel_status_id === PersonnelStatus::ACTIVE)
            ->values()
            ->map(fn (BarangayPersonnel $personnel) => [
                'id' => $personnel->personnel_id,
                'personnel_id' => $personnel->personnel_id,
                'label' => $this->personnelName($personnel) ?? 'Personnel '.$personnel->personnel_id,
            ])
            ->all();
    }

    /**
     * Encode a census visit after household (and optional member) registration.
     *
     * Encoder, visit timestamps, and previous_assessment_id are generated here.
     * Census status, interviewer, supervisor, and next visit date (Callback only)
     * come from the request.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createAssessment(User $performedBy, Household $household, array $data): array
    {
        $encoderId = $performedBy->personnel_id;

        if ($encoderId === null) {
            throw new ConflictHttpException(
                'Encoding an assessment requires a linked barangay personnel record so the encoder can be stored.',
            );
        }

        $censusStatusId = (int) $data['census_status_id'];
        $nextVisitDate = $censusStatusId === CensusStatus::CALLBACK
            ? ($data['next_visit_date'] ?? null)
            : null;

        $previous = $this->householdAssessmentRepository->latestByHousehold($household->household_id);
        $visitEnd = now();
        $visitStart = $previous === null && $household->registration_date !== null
            ? $household->registration_date
            : $visitEnd;

        return DB::transaction(function () use (
            $performedBy,
            $household,
            $data,
            $encoderId,
            $censusStatusId,
            $nextVisitDate,
            $previous,
            $visitStart,
            $visitEnd,
        ) {
            $assessment = $this->householdAssessmentRepository->create([
                'household_id' => $household->household_id,
                'census_status_id' => $censusStatusId,
                'visit_start' => $visitStart,
                'visit_end' => $visitEnd,
                'next_visit_date' => $nextVisitDate,
                'interviewer_id' => $data['interviewer_id'],
                'supervisor_id' => $data['supervisor_id'],
                'encoder_id' => $encoderId,
                'previous_assessment_id' => $previous?->assessment_id,
            ]);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $assessment->assessment_id,
                description: 'Create household assessment',
                oldValue: null,
                newValue: $this->censusStatusLabel($assessment->censusStatus) ?? (string) $censusStatusId,
                target: 'household_assessment',
                entity: 'household_assessment',
            );

            return $this->formatAssessment($assessment);
        });
    }

    /**
     * Update census status only, and only while this is still the household's
     * latest assessment with CB (Callback).
     *
     * @return array<string, mixed>
     */
    public function updateStatus(User $performedBy, HouseholdAssessment $assessment, int $censusStatusId): array
    {
        return DB::transaction(function () use ($performedBy, $assessment, $censusStatusId) {
            $locked = $this->householdAssessmentRepository->lockById($assessment->assessment_id);

            if ($locked === null) {
                throw new ModelNotFoundException(
                    "Household assessment [{$assessment->assessment_id}] not found.",
                );
            }

            $latest = $this->householdAssessmentRepository->latestByHousehold($locked->household_id);

            if ($latest === null || (int) $latest->assessment_id !== (int) $locked->assessment_id) {
                throw new ConflictHttpException(
                    'Only the latest household assessment can be updated.',
                );
            }

            if ((int) $locked->census_status_id !== CensusStatus::CALLBACK) {
                throw new ConflictHttpException(
                    'Census status can only be updated while the latest assessment is still CB (Callback).',
                );
            }

            $oldLabel = $this->censusStatusLabel($locked->censusStatus) ?? (string) $locked->census_status_id;
            $updated = $this->householdAssessmentRepository->updateStatus($locked, $censusStatusId);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $updated->assessment_id,
                description: 'Update household assessment status',
                oldValue: $oldLabel,
                newValue: $this->censusStatusLabel($updated->censusStatus) ?? (string) $censusStatusId,
                target: 'census_status',
                entity: 'household_assessment',
            );

            $payload = $this->formatAssessment($updated) ?? [];
            $payload['is_latest'] = true;

            return $payload;
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function formatAssessment(?HouseholdAssessment $assessment, bool $includePrevious = true): ?array
    {
        if ($assessment === null) {
            return null;
        }

        $assessment->loadMissing([
            'censusStatus',
            'encoder',
            'interviewer',
            'supervisor',
            'previousAssessment.censusStatus',
        ]);

        $payload = [
            'assessment_id' => $assessment->assessment_id,
            'household_id' => $assessment->household_id,
            'census_status_id' => $assessment->census_status_id,
            'census_status' => $assessment->censusStatus?->status_name,
            'census_status_code' => $assessment->censusStatus?->status_code,
            'census_status_label' => $this->censusStatusLabel($assessment->censusStatus),
            'visit_start' => $assessment->visit_start?->toDateTimeString(),
            'visit_end' => $assessment->visit_end?->toDateTimeString(),
            'next_visit_date' => $assessment->next_visit_date?->format('Y-m-d'),
            'encoder_id' => $assessment->encoder_id,
            'encoder_name' => $this->personnelName($assessment->encoder),
            'interviewer_id' => $assessment->interviewer_id,
            'interviewer_name' => $this->personnelName($assessment->interviewer),
            'supervisor_id' => $assessment->supervisor_id,
            'supervisor_name' => $this->personnelName($assessment->supervisor),
            'previous_assessment_id' => $assessment->previous_assessment_id,
        ];

        if ($includePrevious) {
            $payload['previous_assessment'] = $this->formatAssessment($assessment->previousAssessment, false);
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatListRecord(HouseholdAssessment $assessment): array
    {
        $payload = $this->formatAssessment($assessment) ?? [];
        $household = $assessment->household;

        $payload['street_name'] = $household?->street?->street_name;
        $payload['house_lot'] = $household?->house_lot;
        $payload['clan_name'] = $household?->clan?->clan_name;
        $payload['head_name'] = $household?->head !== null ? $this->residentName($household->head) : null;

        return $payload;
    }

    private function residentName(Resident $resident): string
    {
        $givenNames = collect([
            $resident->first_name,
            $resident->middle_name,
            $resident->suffix,
        ])->filter()->implode(' ');

        if ($givenNames === '') {
            return $resident->last_name;
        }

        return $resident->last_name.', '.$givenNames;
    }

    private function censusStatusLabel(?CensusStatus $status): ?string
    {
        if ($status === null) {
            return null;
        }

        $code = trim((string) $status->status_code);
        $name = trim((string) $status->status_name);

        if ($code !== '' && $name !== '') {
            return $code.' ('.$name.')';
        }

        return $name !== '' ? $name : ($code !== '' ? $code : null);
    }

    private function personnelName(?BarangayPersonnel $personnel): ?string
    {
        if ($personnel === null) {
            return null;
        }

        $givenNames = collect([
            $personnel->personnel_first_name,
            $personnel->personnel_middle_name,
            $personnel->personnel_suffix,
        ])->filter()->implode(' ');

        if ($givenNames === '') {
            return (string) $personnel->personnel_last_name;
        }

        return $personnel->personnel_last_name.', '.$givenNames;
    }
}
