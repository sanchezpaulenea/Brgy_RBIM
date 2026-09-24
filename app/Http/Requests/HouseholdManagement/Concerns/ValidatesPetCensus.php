<?php

namespace App\Http\Requests\HouseholdManagement\Concerns;

trait ValidatesPetCensus
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function petCensusRules(string $prefix = ''): array
    {
        $field = fn (string $name) => $prefix === '' ? $name : $prefix.$name;

        return [
            $field('specie_id') => ['required', 'integer', 'exists:specie,specie_id'],
            $field('breed_id') => ['required', 'integer', 'exists:breed,breed_id'],
            $field('sex_id') => ['required', 'integer', 'exists:sex,sex_id'],
            $field('pet_date_of_birth') => ['required', 'date', 'before_or_equal:today'],
            $field('is_spay_neuter') => ['required', 'boolean'],
            $field('rabies_vaccination_date') => ['nullable', 'date'],
        ];
    }
}
