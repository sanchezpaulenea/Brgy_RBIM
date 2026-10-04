<?php

namespace App\Repositories\Interfaces\HouseholdManagement;

use App\Models\HouseholdManagement\PetCensus;
use Illuminate\Support\Collection;

interface PetCensusRepositoryInterface
{
    public function findById(int $petCensusId): ?PetCensus;

    /**
     * @param  array{household_id?: int, specie_id?: int, breed_id?: int, sex_id?: int}  $filters
     * @return Collection<int, PetCensus>
     */
    public function list(array $filters = []): Collection;

    /**
     * @return Collection<int, PetCensus>
     */
    public function listByHouseholdId(int $householdId): Collection;

    /**
     * @return array{
     *     households: Collection<int, \App\Models\HouseholdManagement\Household>,
     *     species: Collection<int, \App\Models\HouseholdManagement\Specie>,
     *     breeds: Collection<int, \App\Models\HouseholdManagement\Breed>,
     *     sexes: Collection<int, \App\Models\ResidentManagement\Demographic\Sex>
     * }
     */
    public function filterOptions(): array;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PetCensus;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(PetCensus $pet, array $attributes): PetCensus;
}
