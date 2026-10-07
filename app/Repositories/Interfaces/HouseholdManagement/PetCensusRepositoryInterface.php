<?php

namespace App\Repositories\Interfaces\HouseholdManagement;

use App\Models\HouseholdManagement\Breed;
use App\Models\HouseholdManagement\PetCensus;
use App\Models\HouseholdManagement\PetStatus;
use App\Models\HouseholdManagement\Specie;
use App\Models\ResidentManagement\Demographic\Sex;
use Illuminate\Support\Collection;

interface PetCensusRepositoryInterface
{
    public function findById(int $petCensusId): ?PetCensus;

    /**
     * @param  array{search?: string, pet_status_id?: int, specie_id?: int, breed_id?: int}  $filters
     * @return Collection<int, PetCensus>
     */
    public function list(array $filters = []): Collection;

    /**
     * @return Collection<int, PetCensus>
     */
    public function listByHouseholdId(int $householdId): Collection;

    /**
     * @return array{
     *     pet_statuses: Collection<int, PetStatus>,
     *     species: Collection<int, Specie>,
     *     breeds: Collection<int, Breed>,
     *     sexes: Collection<int, Sex>
     * }
     */
    public function filterOptions(): array;

    public function findOrCreateSpecieId(string $label): int;

    public function findOrCreateBreedId(string $label): int;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PetCensus;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(PetCensus $pet, array $attributes): PetCensus;
}
