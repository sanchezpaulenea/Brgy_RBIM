<?php

namespace App\Repositories\HouseholdManagement;

use App\Models\HouseholdManagement\Breed;
use App\Models\HouseholdManagement\PetCensus;
use App\Models\HouseholdManagement\PetStatus;
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
        return ['petStatus', 'specie', 'breed', 'sex', 'household.head', 'household.street'];
    }

    /**
     * @param  array{search?: string, pet_status_id?: int, specie_id?: int, breed_id?: int}  $filters
     * @return Collection<int, PetCensus>
     */
    public function list(array $filters = []): Collection
    {
        return PetCensus::query()
            ->with($this->defaultRelations())
            ->when(
                ! empty($filters['pet_status_id']),
                fn ($query) => $query->where('pet_status_id', $filters['pet_status_id']),
            )
            ->when(
                ! empty($filters['specie_id']),
                fn ($query) => $query->where('specie_id', $filters['specie_id']),
            )
            ->when(
                ! empty($filters['breed_id']),
                fn ($query) => $query->where('breed_id', $filters['breed_id']),
            )
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $term = '%'.$this->escapeLike(mb_strtolower(trim((string) $filters['search']))).'%';

                $query->where(function ($searchQuery) use ($term) {
                    $searchQuery
                        ->whereRaw('CAST(pet_census_id AS CHAR) LIKE ? ESCAPE \'\\\\\'', [$term])
                        ->orWhereHas(
                            'specie',
                            fn ($specieQuery) => $specieQuery->whereRaw(
                                'LOWER(specie) LIKE ? ESCAPE \'\\\\\'',
                                [$term],
                            ),
                        )
                        ->orWhereHas(
                            'breed',
                            fn ($breedQuery) => $breedQuery->whereRaw(
                                'LOWER(breed) LIKE ? ESCAPE \'\\\\\'',
                                [$term],
                            ),
                        )
                        ->orWhereHas('household', function ($householdQuery) use ($term) {
                            $householdQuery
                                ->whereRaw('CAST(household_id AS CHAR) LIKE ? ESCAPE \'\\\\\'', [$term])
                                ->orWhereRaw('LOWER(COALESCE(house_lot, \'\')) LIKE ? ESCAPE \'\\\\\'', [$term])
                                ->orWhereHas(
                                    'street',
                                    fn ($streetQuery) => $streetQuery->whereRaw(
                                        'LOWER(street_name) LIKE ? ESCAPE \'\\\\\'',
                                        [$term],
                                    ),
                                )
                                ->orWhereHas('head', function ($headQuery) use ($term) {
                                    $headQuery
                                        ->whereRaw('LOWER(last_name) LIKE ? ESCAPE \'\\\\\'', [$term])
                                        ->orWhereRaw('LOWER(first_name) LIKE ? ESCAPE \'\\\\\'', [$term])
                                        ->orWhereRaw(
                                            'LOWER(CONCAT(last_name, \', \', first_name)) LIKE ? ESCAPE \'\\\\\'',
                                            [$term],
                                        )
                                        ->orWhereRaw(
                                            'LOWER(CONCAT(last_name, \', \', first_name, \' \', COALESCE(middle_name, \'\'))) LIKE ? ESCAPE \'\\\\\'',
                                            [$term],
                                        );
                                });
                        });
                });
            })
            ->orderBy('pet_census_id')
            ->get();
    }

    /**
     * @return array{
     *     pet_statuses: Collection<int, PetStatus>,
     *     species: Collection<int, Specie>,
     *     breeds: Collection<int, Breed>,
     *     sexes: Collection<int, Sex>
     * }
     */
    public function filterOptions(): array
    {
        return [
            'pet_statuses' => PetStatus::query()->orderBy('pet_status_id')->get(),
            'species' => Specie::query()->orderBy('specie')->orderBy('specie_id')->get(),
            'breeds' => Breed::query()->orderBy('breed')->orderBy('breed_id')->get(),
            'sexes' => Sex::query()->orderBy('sex')->orderBy('sex_id')->get(),
        ];
    }

    public function findOrCreateSpecieId(string $label): int
    {
        return Specie::findOrCreateByLabel($label)->specie_id;
    }

    public function findOrCreateBreedId(string $label): int
    {
        return Breed::findOrCreateByLabel($label)->breed_id;
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

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
