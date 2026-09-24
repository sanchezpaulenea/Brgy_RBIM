<?php

namespace App\Repositories\HouseholdManagement;

use App\Models\HouseholdManagement\PetCensus;
use App\Repositories\Interfaces\HouseholdManagement\PetCensusRepositoryInterface;
use Illuminate\Support\Collection;

class PetCensusRepository implements PetCensusRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return ['specie', 'breed', 'sex'];
    }

    public function findById(int $petCensusId): ?PetCensus
    {
        return PetCensus::query()
            ->with($this->defaultRelations())
            ->where('pet_census_id', $petCensusId)
            ->first();
    }

    public function listByHouseholdId(int $householdId): Collection
    {
        return PetCensus::query()
            ->with($this->defaultRelations())
            ->where('household_id', $householdId)
            ->orderBy('pet_census_id')
            ->get();
    }

    public function create(array $attributes): PetCensus
    {
        $pet = PetCensus::create($attributes);

        return $pet->load($this->defaultRelations());
    }

    public function update(PetCensus $pet, array $attributes): PetCensus
    {
        $pet->fill($attributes);
        $pet->save();

        return $pet->fresh($this->defaultRelations()) ?? $pet;
    }
}
