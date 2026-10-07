<?php

namespace App\Services\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\PetCensus;
use App\Models\Logs\Action;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\HouseholdManagement\PetCensusRepositoryInterface;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Services\ResidentManagement\Concerns\LogsAuditableFieldChanges;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PetCensusService
{
    use LogsAuditableFieldChanges;

    public function __construct(
        protected PetCensusRepositoryInterface $petCensusRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @param  array{search?: string, pet_status_id?: int, specie_id?: int, breed_id?: int}  $filters
     * @return array{items: list<array<string, mixed>>, options: array<string, list<array<string, mixed>>>}
     */
    public function list(array $filters = []): array
    {
        $options = $this->petCensusRepository->filterOptions();

        return [
            'items' => $this->petCensusRepository->list($filters)
                ->map(fn (PetCensus $pet) => $this->formatRecord($pet))
                ->values()
                ->all(),
            'options' => [
                'pet_statuses' => $options['pet_statuses']
                    ->map(fn ($status) => [
                        'id' => $status->pet_status_id,
                        'label' => $status->pet_status,
                    ])
                    ->values()
                    ->all(),
                'species' => $options['species']
                    ->map(fn ($specie) => [
                        'id' => $specie->specie_id,
                        'label' => $specie->specie,
                    ])
                    ->values()
                    ->all(),
                'breeds' => $options['breeds']
                    ->map(fn ($breed) => [
                        'id' => $breed->breed_id,
                        'label' => $breed->breed,
                    ])
                    ->values()
                    ->all(),
                'sexes' => $options['sexes']
                    ->map(fn ($sex) => [
                        'id' => $sex->sex_id,
                        'label' => $sex->sex,
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    }

    /**
     * Household update must not create pet rows. Entries without a pet that
     * already belongs to the household are rejected.
     *
     * @param  list<array<string, mixed>>  $pets
     */
    public function rejectPetsThatAreNotOnHousehold(Household $household, array $pets): void
    {
        foreach ($pets as $index => $pet) {
            $petCensusId = is_array($pet) ? ($pet['pet_census_id'] ?? null) : null;

            if (! is_numeric($petCensusId)) {
                throw ValidationException::withMessages([
                    "pets.{$index}.pet_census_id" => 'Add pets from the Pet Census tab.',
                ]);
            }

            $existing = $this->petCensusRepository->findById((int) $petCensusId);

            if ($existing === null || (int) $existing->household_id !== (int) $household->household_id) {
                throw ValidationException::withMessages([
                    "pets.{$index}.pet_census_id" => 'The pet does not belong to this household.',
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    public function createMany(User $performedBy, Household $household, array $data): array
    {
        return DB::transaction(function () use ($performedBy, $household, $data) {
            $created = [];

            foreach ($data['pets'] as $petData) {
                $pet = $this->petCensusRepository->create(
                    $this->persistableAttributes($petData, $household->household_id),
                );

                $this->auditLogRepository->log(
                    performedByUserId: $performedBy->user_id,
                    actionId: Action::CREATE,
                    recordId: $pet->pet_census_id,
                    description: 'Create pet census',
                    oldValue: null,
                    newValue: 'Household '.$household->household_id,
                    target: 'record',
                    entity: 'pet_census',
                );

                $created[] = $this->formatRecord(
                    $this->petCensusRepository->findById($pet->pet_census_id) ?? $pet,
                );
            }

            return $created;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(User $performedBy, PetCensus $pet, array $data): array
    {
        $previous = $this->auditSnapshot($pet);

        return DB::transaction(function () use ($performedBy, $pet, $data, $previous) {
            $updated = $this->petCensusRepository->update(
                $pet,
                $this->persistableAttributes($data),
            );

            $fresh = $this->petCensusRepository->findById($updated->pet_census_id) ?? $updated;

            $this->logFieldChanges(
                $performedBy,
                $fresh->pet_census_id,
                'pet_census',
                $previous,
                $this->auditSnapshot($fresh),
                'Updated pet census',
            );

            return $this->formatRecord($fresh);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(PetCensus $pet): array
    {
        $pet->loadMissing(['petStatus', 'specie', 'breed', 'sex']);

        return [
            'pet_census_id' => $pet->pet_census_id,
            'household_id' => $pet->household_id,
            'household_label' => $this->householdLabel($pet->household, $pet->household_id),
            'specie_id' => $pet->specie_id,
            'specie' => $pet->specie?->specie,
            'breed_id' => $pet->breed_id,
            'breed' => $pet->breed?->breed,
            'sex_id' => $pet->sex_id,
            'sex' => $pet->sex?->sex,
            'pet_date_of_birth' => $pet->pet_date_of_birth?->format('Y-m-d'),
            'age' => $this->ageInYears($pet->pet_date_of_birth),
            'is_spay_neuter' => (bool) $pet->is_spay_neuter,
            'rabies_vaccination_date' => $pet->rabies_vaccination_date?->format('Y-m-d'),
            'pet_status_id' => $pet->pet_status_id,
            'pet_status' => $pet->petStatus?->pet_status,
        ];
    }

    private function householdLabel(?Household $household, ?int $householdId = null): string
    {
        $id = $household?->household_id ?? $householdId;
        $head = $household?->head;
        $name = $head instanceof Resident ? $this->residentName($head) : '';

        if ($name === '') {
            return 'Household '.$id;
        }

        return 'Household '.$id.' — '.$name;
    }

    private function residentName(Resident $resident): string
    {
        $givenNames = collect([
            $resident->first_name,
            $resident->middle_name,
            $resident->suffix,
        ])->filter()->implode(' ');

        if ($givenNames === '') {
            return (string) $resident->last_name;
        }

        return $resident->last_name.', '.$givenNames;
    }

    private function ageInYears(?CarbonInterface $dateOfBirth): ?int
    {
        if ($dateOfBirth === null) {
            return null;
        }

        $today = now()->startOfDay();
        $birth = $dateOfBirth->copy()->startOfDay();
        $age = $today->year - $birth->year;

        if ($today->month < $birth->month || ($today->month === $birth->month && $today->day < $birth->day)) {
            $age--;
        }

        return max($age, 0);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function persistableAttributes(array $data, ?int $householdId = null): array
    {
        $attributes = [
            'specie_id' => $this->petCensusRepository->findOrCreateSpecieId($this->titleCaseLookup((string) $data['specie'])),
            'breed_id' => $this->petCensusRepository->findOrCreateBreedId($this->titleCaseLookup((string) $data['breed'])),
            'sex_id' => $data['sex_id'],
            'pet_date_of_birth' => $data['pet_date_of_birth'],
            'is_spay_neuter' => $data['is_spay_neuter'],
            'rabies_vaccination_date' => $data['rabies_vaccination_date'] ?? null,
        ];

        if (array_key_exists('pet_status_id', $data) && $data['pet_status_id'] !== null && $data['pet_status_id'] !== '') {
            $attributes['pet_status_id'] = $data['pet_status_id'];
        }

        if ($householdId !== null) {
            $attributes['household_id'] = $householdId;
        }

        return $attributes;
    }

    private function titleCaseLookup(string $label): string
    {
        return Str::of($label)->squish()->title()->toString();
    }

    /**
     * @return array<string, string>
     */
    private function auditSnapshot(PetCensus $pet): array
    {
        $pet->loadMissing(['petStatus', 'specie', 'breed', 'sex']);

        return [
            'specie' => (string) ($pet->specie?->specie ?? ''),
            'breed' => (string) ($pet->breed?->breed ?? ''),
            'sex' => (string) ($pet->sex?->sex ?? ''),
            'pet_date_of_birth' => (string) ($pet->pet_date_of_birth?->format('Y-m-d') ?? ''),
            'is_spay_neuter' => $pet->is_spay_neuter ? 'Yes' : 'No',
            'rabies_vaccination_date' => (string) ($pet->rabies_vaccination_date?->format('Y-m-d') ?? ''),
            'pet_status' => (string) ($pet->petStatus?->pet_status ?? ''),
        ];
    }
}
