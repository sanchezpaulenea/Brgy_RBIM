<?php

namespace App\Services\HouseholdManagement;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\HouseholdManagement\CensusStatus;
use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\HouseholdAssessment;
use App\Models\HouseholdManagement\HouseholdStatus;
use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\RelationshipToHouseholdHead;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\ResidentStatus;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdAssessmentRepositoryInterface;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ResidentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class HouseholdServices
{
    public function __construct(
        protected HouseholdRepositoryInterface $householdRepository,
        protected ResidentRepositoryInterface $residentRepository,
        protected HouseholdAssessmentRepositoryInterface $householdAssessmentRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * Register a household and its head resident in one atomic transaction.
     *
     * household.head_resident_id and resident.household_id are circular NOT NULL
     * foreign keys. MySQL cannot defer those checks, so FK enforcement is turned
     * off only for the two inserts (and the head pointer update) inside this
     * transaction, then restored in finally so the connection never leaks the
     * disabled state.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createHousehold(User $performedBy, array $data): array
    {
        $headData = $data['head'];

        return DB::transaction(function () use ($performedBy, $data, $headData) {
            $household = null;
            $resident = null;

            try {
                $this->disableForeignKeyChecks();

                $household = $this->householdRepository->create([
                    'clan_id' => $data['clan_id'],
                    'street_id' => $data['street_id'],
                    'house_lot' => $data['house_lot'] ?? null,
                    'block_num' => $data['block_num'] ?? null,
                    'building_name' => $data['building_name'] ?? null,
                    'unit_num' => $data['unit_num'] ?? null,
                    'registration_date' => now(),
                    'household_status_id' => $data['household_status_id'] ?? HouseholdStatus::ACTIVE,
                    'head_resident_id' => 0,
                ]);

                $resident = $this->residentRepository->create([
                    'last_name' => $headData['last_name'],
                    'first_name' => $headData['first_name'],
                    'middle_name' => $headData['middle_name'] ?? null,
                    'suffix' => $headData['suffix'] ?? null,
                    'relationship_to_hh_id' => RelationshipToHouseholdHead::HEAD,
                    'sex_id' => $headData['sex_id'],
                    'date_of_birth' => $headData['date_of_birth'],
                    'birth_city_municipality' => $headData['birth_city_municipality'],
                    'birth_province' => $headData['birth_province'],
                    'birth_country' => $headData['birth_country'],
                    'nationality_id' => $headData['nationality_id'],
                    'religion_id' => $headData['religion_id'],
                    'ethnicity_id' => $headData['ethnicity_id'],
                    'marital_status_id' => $headData['marital_status_id'],
                    'resident_type_id' => $headData['resident_type_id'],
                    'clan_id' => $headData['clan_id'] ?? $data['clan_id'],
                    'resident_status_id' => $headData['resident_status_id'] ?? ResidentStatus::ACTIVE,
                    'household_id' => $household->household_id,
                ]);

                $household = $this->householdRepository->updateHeadResident(
                    $household,
                    $resident->resident_id,
                );
            } finally {
                $this->enableForeignKeyChecks();
            }

            if ($household === null || $resident === null) {
                throw new \RuntimeException('Household registration did not produce both records.');
            }

            $assessment = $this->createInitialAssessment($performedBy, $household);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $household->household_id,
                description: 'Create household',
                oldValue: null,
                newValue: $this->householdAuditLabel($household),
                target: 'household',
                entity: 'household',
            );

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $resident->resident_id,
                description: 'Create resident',
                oldValue: null,
                newValue: $this->fullName($resident),
                target: 'record',
                entity: 'resident',
            );

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $assessment->assessment_id,
                description: 'Create household assessment',
                oldValue: null,
                newValue: (string) ($assessment->censusStatus?->status_name ?? CensusStatus::COMPLETED),
                target: 'household_assessment',
                entity: 'household_assessment',
            );

            $household->setRelation('latestAssessment', $assessment);

            return $this->formatRecord($household);
        });
    }

    /**
     * First census visit for a newly registered household.
     *
     * census_status has no pending/in-progress value (only Completed, Callback,
     * Refused). Completed is the registration outcome; extra members added
     * afterward are still the same visit, not a new assessment.
     *
     * visit_end is NOT NULL and defaults to CURRENT_TIMESTAMP, so it is stamped
     * at registration. Leaving it open until "Finish" would require a schema
     * change.
     *
     * interviewer_id and supervisor_id currently copy encoder_id as a
     * placeholder until the registration form collects them separately.
     */
    private function createInitialAssessment(User $performedBy, Household $household): HouseholdAssessment
    {
        $personnelId = $performedBy->personnel_id;

        if ($personnelId === null) {
            throw new ConflictHttpException(
                'Household registration requires a linked barangay personnel record so encoder, interviewer, and supervisor can be stored.',
            );
        }

        $visitedAt = now();

        return $this->householdAssessmentRepository->create([
            'household_id' => $household->household_id,
            'census_status_id' => CensusStatus::COMPLETED,
            'visit_start' => $visitedAt,
            'visit_end' => $visitedAt,
            'next_visit_date' => null,
            'interviewer_id' => $personnelId,
            'supervisor_id' => $personnelId,
            'encoder_id' => $personnelId,
            'previous_assessment_id' => null,
        ]);
    }

    /**
     * @param  array{street_id?: int, household_status_id?: int}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function listHouseholds(array $filters = []): array
    {
        return $this->householdRepository
            ->list($filters)
            ->map(fn (Household $household) => $this->formatListRecord($household))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getHousehold(Household $household): array
    {
        $fresh = $this->householdRepository->findById($household->household_id, true);

        return $this->formatRecord($fresh ?? $household, includeResidents: true);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateHousehold(User $performedBy, Household $household, array $data): array
    {
        unset($data['head_resident_id'], $data['head']);

        $previous = $this->householdAuditSnapshot($household);

        return DB::transaction(function () use ($performedBy, $household, $data, $previous) {
            $updated = $this->householdRepository->update($household, $data);

            $this->logHouseholdFieldChanges($performedBy, $updated, $previous);

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatListRecord(Household $household): array
    {
        $household->loadMissing(['clan', 'street', 'status', 'head']);

        return [
            'household_id' => $household->household_id,
            'clan_id' => $household->clan_id,
            'clan_name' => $household->clan?->clan_name,
            'street_id' => $household->street_id,
            'street_name' => $household->street?->street_name,
            'house_lot' => $household->house_lot,
            'block_num' => $household->block_num,
            'building_name' => $household->building_name,
            'unit_num' => $household->unit_num,
            'registration_date' => $household->registration_date?->toDateTimeString(),
            'household_status_id' => $household->household_status_id,
            'household_status' => $household->status?->household_status,
            'head_resident_id' => $household->head_resident_id,
            'head_name' => $household->head !== null ? $this->fullName($household->head) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Household $household, bool $includeResidents = false): array
    {
        $household->loadMissing([
            'clan',
            'street',
            'status',
            'head.sex',
            'head.nationality',
            'head.religion',
            'head.ethnicity',
            'head.maritalStatus',
            'head.residentType',
            'head.status',
            'head.clan',
            'head.relationshipToHouseholdHead',
            'latestAssessment.censusStatus',
            'latestAssessment.encoder',
            'latestAssessment.interviewer',
            'latestAssessment.supervisor',
        ]);

        $payload = [
            'household_id' => $household->household_id,
            'clan_id' => $household->clan_id,
            'clan_name' => $household->clan?->clan_name,
            'street_id' => $household->street_id,
            'street_name' => $household->street?->street_name,
            'house_lot' => $household->house_lot,
            'block_num' => $household->block_num,
            'building_name' => $household->building_name,
            'unit_num' => $household->unit_num,
            'registration_date' => $household->registration_date?->toDateTimeString(),
            'household_status_id' => $household->household_status_id,
            'household_status' => $household->status?->household_status,
            'head_resident_id' => $household->head_resident_id,
            'head' => $household->head !== null ? $this->formatResident($household->head) : null,
            'latest_assessment' => $this->formatAssessment($household->latestAssessment),
        ];

        if ($includeResidents) {
            $household->loadMissing([
                'residents.sex',
                'residents.nationality',
                'residents.religion',
                'residents.ethnicity',
                'residents.maritalStatus',
                'residents.residentType',
                'residents.status',
                'residents.clan',
                'residents.relationshipToHouseholdHead',
            ]);

            $payload['residents'] = $household->residents
                ->map(fn (Resident $resident) => $this->formatResident($resident))
                ->values()
                ->all();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function formatAssessment(?HouseholdAssessment $assessment): ?array
    {
        if ($assessment === null) {
            return null;
        }

        $assessment->loadMissing(['censusStatus', 'encoder', 'interviewer', 'supervisor']);

        return [
            'assessment_id' => $assessment->assessment_id,
            'census_status_id' => $assessment->census_status_id,
            'census_status' => $assessment->censusStatus?->status_name,
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

    /**
     * @return array<string, mixed>
     */
    private function formatResident(Resident $resident): array
    {
        return [
            'resident_id' => $resident->resident_id,
            'last_name' => $resident->last_name,
            'first_name' => $resident->first_name,
            'middle_name' => $resident->middle_name,
            'suffix' => $resident->suffix,
            'full_name' => $this->fullName($resident),
            'relationship_to_hh_id' => $resident->relationship_to_hh_id,
            'relationship_to_hh' => $resident->relationshipToHouseholdHead?->relationship_to_hh,
            'sex_id' => $resident->sex_id,
            'sex' => $resident->sex?->sex,
            'date_of_birth' => $resident->date_of_birth?->format('Y-m-d'),
            'birth_city_municipality' => $resident->birth_city_municipality,
            'birth_province' => $resident->birth_province,
            'birth_country' => $resident->birth_country,
            'nationality_id' => $resident->nationality_id,
            'nationality' => $resident->nationality?->nationality,
            'religion_id' => $resident->religion_id,
            'religion' => $resident->religion?->religion,
            'ethnicity_id' => $resident->ethnicity_id,
            'ethnicity' => $resident->ethnicity?->ethnicity,
            'marital_status_id' => $resident->marital_status_id,
            'marital_status' => $resident->maritalStatus?->marital_status,
            'resident_type_id' => $resident->resident_type_id,
            'resident_type' => $resident->residentType?->resident_type,
            'clan_id' => $resident->clan_id,
            'clan_name' => $resident->clan?->clan_name,
            'resident_status_id' => $resident->resident_status_id,
            'resident_status' => $resident->status?->resident_status,
            'household_id' => $resident->household_id,
        ];
    }

    private function householdAuditLabel(Household $household): string
    {
        if ($household->head !== null) {
            return $this->fullName($household->head);
        }

        return 'Household '.$household->household_id;
    }

    private function fullName(Resident $resident): string
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

    /**
     * @return array{
     *     clan: string,
     *     street: string,
     *     house lot: string,
     *     block num: string,
     *     building name: string,
     *     unit num: string,
     *     status: string
     * }
     */
    private function householdAuditSnapshot(Household $household): array
    {
        $household->loadMissing(['clan', 'street', 'status']);

        return [
            'clan' => (string) ($household->clan?->clan_name ?? $household->clan_id),
            'street' => (string) ($household->street?->street_name ?? $household->street_id),
            'house lot' => (string) ($household->house_lot ?? ''),
            'block num' => (string) ($household->block_num ?? ''),
            'building name' => (string) ($household->building_name ?? ''),
            'unit num' => (string) ($household->unit_num ?? ''),
            'status' => (string) ($household->status?->household_status ?? $household->household_status_id),
        ];
    }

    /**
     * @param  array{
     *     clan: string,
     *     street: string,
     *     house lot: string,
     *     block num: string,
     *     building name: string,
     *     unit num: string,
     *     status: string
     * }  $previous
     */
    private function logHouseholdFieldChanges(User $performedBy, Household $updated, array $previous): void
    {
        $current = $this->householdAuditSnapshot($updated);

        foreach ($current as $target => $newValue) {
            $oldValue = $previous[$target] ?? '';

            if ($oldValue === $newValue) {
                continue;
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $updated->household_id,
                description: 'Updated household '.$target,
                oldValue: $oldValue,
                newValue: $newValue,
                target: $target,
                entity: 'household',
            );
        }
    }

    private function disableForeignKeyChecks(): void
    {
        if (! $this->usesMysql()) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    private function enableForeignKeyChecks(): void
    {
        if (! $this->usesMysql()) {
            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function usesMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
}
