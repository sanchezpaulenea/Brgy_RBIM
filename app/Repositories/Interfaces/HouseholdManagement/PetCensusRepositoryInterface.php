<?php

namespace App\Repositories\Interfaces\HouseholdManagement;

use App\Models\HouseholdManagement\PetCensus;
use Illuminate\Support\Collection;

interface PetCensusRepositoryInterface
{
    public function findById(int $petCensusId): ?PetCensus;

    /**
     * @return Collection<int, PetCensus>
     */
    public function listByHouseholdId(int $householdId): Collection;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PetCensus;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(PetCensus $pet, array $attributes): PetCensus;
}
