<?php

namespace App\Services\ResidentManagement\Demographic;

use App\Models\HouseholdManagement\Household;
use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\ResidentStatus;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ResidentRepositoryInterface;
use App\Services\ResidentManagement\Ctc\CtcService;
use App\Services\ResidentManagement\Economic\EconomicService;
use App\Services\ResidentManagement\Education\EducationService;
use App\Services\ResidentManagement\Health\HealthService;
use App\Services\ResidentManagement\Health\InfantHealthService;
use App\Services\ResidentManagement\Health\WomenHealthService;
use App\Services\ResidentManagement\Migration\MigrationService;
use App\Services\ResidentManagement\Skill\SkillService;
use App\Services\ResidentManagement\Sociocivic\SociocivicService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResidentServices
{
    public function __construct(
        protected ResidentRepositoryInterface $residentRepository,
        protected HouseholdRepositoryInterface $householdRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
        protected EducationService $educationService,
        protected EconomicService $economicService,
        protected InfantHealthService $infantHealthService,
        protected HealthService $healthService,
        protected WomenHealthService $womenHealthService,
        protected SociocivicService $sociocivicService,
        protected MigrationService $migrationService,
        protected CtcService $ctcService,
        protected SkillService $skillService,
    ) {}

    /**
     * @param  array{
     *     search?: string,
     *     household_id?: int,
     *     sex_id?: int,
     *     resident_type_id?: int,
     *     resident_status_id?: int,
     *     age_min?: int,
     *     age_max?: int
     * }  $filters
     * @return array<int, array<string, mixed>>
     */
    public function listResidents(array $filters = []): array
    {
        return $this->residentRepository
            ->list($filters)
            ->map(fn (Resident $resident) => $this->formatRecord($resident))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getResident(Resident $resident): array
    {
        $fresh = $this->residentRepository->findById($resident->resident_id);

        return $this->formatRecord($fresh ?? $resident, includeSubRecords: true);
    }

    /**
     * Register an additional resident into an existing household.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createResident(User $performedBy, array $data): array
    {
        $household = $this->requireHousehold((int) $data['household_id']);

        $data['clan_id'] = $data['clan_id'] ?? $household->clan_id;
        $data['resident_status_id'] = $data['resident_status_id'] ?? ResidentStatus::ACTIVE;

        return DB::transaction(function () use ($performedBy, $data) {
            $resident = $this->residentRepository->create($data);

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

            return $this->formatRecord($resident);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function updateResident(User $performedBy, Resident $resident, array $data): array
    {
        $this->assertHouseholdMoveAllowed($resident, $data);

        $previous = $this->residentAuditSnapshot($resident);

        return DB::transaction(function () use ($performedBy, $resident, $data, $previous) {
            $updated = $this->residentRepository->update($resident, $data);

            $this->logResidentFieldChanges($performedBy, $updated, $previous);

            return $this->formatRecord($updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Resident $resident, bool $includeSubRecords = false): array
    {
        $resident->loadMissing([
            'household',
            'clan',
            'sex',
            'nationality',
            'religion',
            'ethnicity',
            'maritalStatus',
            'residentType',
            'status',
            'relationshipToHouseholdHead',
        ]);

        $payload = [
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
            'age' => $resident->age(),
            'age_in_months' => $resident->ageInMonths(),
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
            'is_household_head' => (int) ($resident->household?->head_resident_id ?? 0) === (int) $resident->resident_id,
            'applicable_sections' => $resident->applicableSections(),
            'profiling_completeness' => $resident->profilingCompleteness(),
        ];

        if ($includeSubRecords) {
            $payload['education'] = $resident->education
                ? $this->educationService->formatRecord($resident->education)
                : null;
            $payload['economic'] = $resident->economic
                ? $this->economicService->formatRecord($resident->economic)
                : null;
            $payload['infant_health'] = $resident->infantHealth
                ? $this->infantHealthService->formatRecord($resident->infantHealth)
                : null;
            $payload['health'] = $resident->health
                ? $this->healthService->formatRecord($resident->health)
                : null;
            $payload['women_health'] = $resident->health?->womenHealth
                ? $this->womenHealthService->formatRecord($resident->health->womenHealth)
                : null;
            $payload['sociocivic'] = $resident->sociocivic
                ? $this->sociocivicService->formatRecord($resident->sociocivic)
                : null;
            $payload['migration'] = $resident->migration
                ? $this->migrationService->formatRecord($resident->migration)
                : null;
            $payload['ctc'] = $resident->communityTaxCert
                ? $this->ctcService->formatRecord($resident->communityTaxCert)
                : null;
            $payload['skills'] = $resident->skillsDevelopment
                ? $this->skillService->formatRecord($resident->skillsDevelopment)
                : null;
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertHouseholdMoveAllowed(Resident $resident, array $data): void
    {
        if (! array_key_exists('household_id', $data)) {
            return;
        }

        $newHouseholdId = (int) $data['household_id'];

        if ($newHouseholdId === (int) $resident->household_id) {
            return;
        }

        $resident->loadMissing('household');

        if ($resident->household?->head_resident_id === $resident->resident_id) {
            throw ValidationException::withMessages([
                'household_id' => ['The household head cannot be moved to another household on this update.'],
            ]);
        }

        $this->requireHousehold($newHouseholdId);
    }

    private function requireHousehold(int $householdId): Household
    {
        $household = $this->householdRepository->findById($householdId);

        if ($household === null) {
            throw ValidationException::withMessages([
                'household_id' => ['The selected household does not exist.'],
            ]);
        }

        return $household;
    }

    /**
     * @return array<string, string>
     */
    private function residentAuditSnapshot(Resident $resident): array
    {
        $resident->loadMissing([
            'relationshipToHouseholdHead',
            'sex',
            'nationality',
            'religion',
            'ethnicity',
            'maritalStatus',
            'residentType',
            'clan',
            'status',
        ]);

        return [
            'name' => $this->fullName($resident),
            'relationship' => (string) ($resident->relationshipToHouseholdHead?->relationship_to_hh ?? $resident->relationship_to_hh_id),
            'sex' => (string) ($resident->sex?->sex ?? $resident->sex_id),
            'birth date' => $resident->date_of_birth?->format('Y-m-d') ?? '',
            'birth city' => (string) $resident->birth_city_municipality,
            'birth province' => (string) $resident->birth_province,
            'birth country' => (string) $resident->birth_country,
            'nationality' => (string) ($resident->nationality?->nationality ?? $resident->nationality_id),
            'religion' => (string) ($resident->religion?->religion ?? $resident->religion_id),
            'ethnicity' => (string) ($resident->ethnicity?->ethnicity ?? $resident->ethnicity_id),
            'marital status' => (string) ($resident->maritalStatus?->marital_status ?? $resident->marital_status_id),
            'resident type' => (string) ($resident->residentType?->resident_type ?? $resident->resident_type_id),
            'clan' => (string) ($resident->clan?->clan_name ?? $resident->clan_id),
            'status' => (string) ($resident->status?->resident_status ?? $resident->resident_status_id),
            'household' => (string) $resident->household_id,
        ];
    }

    /**
     * @param  array<string, string>  $previous
     */
    private function logResidentFieldChanges(User $performedBy, Resident $updated, array $previous): void
    {
        $current = $this->residentAuditSnapshot($updated);

        foreach ($current as $target => $newValue) {
            $oldValue = $previous[$target] ?? '';

            if ($oldValue === $newValue) {
                continue;
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $updated->resident_id,
                description: 'Updated resident '.$target,
                oldValue: $oldValue,
                newValue: $newValue,
                target: $target,
                entity: 'resident',
            );
        }
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
}
