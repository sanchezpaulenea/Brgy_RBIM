<?php

namespace App\Repositories\HouseholdManagement;

use App\Models\HouseholdManagement\Breed;
use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\PetCensus;
use App\Models\HouseholdManagement\Specie;
use App\Models\ResidentManagement\Demographic\Sex;
use App\Repositories\Interfaces\HouseholdManagement\PetCensusRepositoryInterface;
use Illuminate\Support\Collection;

class PetCensusRepository implements PetCensusRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return ['specie', 'breed', 'sex', 'household.head', 'household.street'];
    }

    /**
     * @param  array{household_id?: int, specie_id?: int, breed_id?: int, sex_id?: int}  $filters
     * @return Collection<int, PetCensus>
     */
    public function list(array $filters = []): Collection
    {
        return PetCensus::query()
            ->with($this->defaultRelations())
            ->when(
                ! empty($filters['household_id']),
                fn ($query) => $query->where('household_id', $filters['household_id']),
            )
            ->when(
                ! empty($filters['specie_id']),
                fn ($query) => $query->where('specie_id', $filters['specie_id']),
            )
            ->when(
                ! empty($filters['breed_id']),
                fn ($query) => $query->where('breed_id', $filters['breed_id']),
            )
            ->when(
                ! empty($filters['sex_id']),
                fn ($query) => $query->where('sex_id', $filters['sex_id']),
            )
            ->orderBy('pet_census_id')
            ->get();
    }

    /**
     * @return array{
     *     households: Collection<int, Household>,
     *     species: Collection<int, Specie>,
     *     breeds: Collection<int, Breed>,
     *     sexes: Collection<int, Sex>
     * }
     */
    public function filterOptions(): array
    {
        return [
            'households' => Household::query()->with('head')->orderBy('household_id')->get(),
            'species' => Specie::query()->orderBy('specie')->orderBy('specie_id')->get(),
            'breeds' => Breed::query()->orderBy('breed')->orderBy('breed_id')->get(),
            'sexes' => Sex::query()->orderBy('sex')->orderBy('sex_id')->get(),
        ];
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
