<?php

namespace App\Http\Requests\HouseholdManagement\Concerns;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesPetCensus
{
    public const MAX_PETS_PER_REQUEST = 10;

    protected function prepareForValidation(): void
    {
        if ($this->exists('pets') && is_array($this->input('pets'))) {
            $pets = [];

            foreach ($this->input('pets') as $pet) {
                $pets[] = is_array($pet) ? $this->normalizePetInput($pet) : $pet;
            }

            $this->merge(['pets' => $pets]);
        }

        if ($this->exists('specie') || $this->exists('breed') || $this->exists('rabies_vaccination_date')) {
            $this->merge($this->normalizePetInput($this->all()));
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function petCensusRules(string $prefix = '', bool $requireStatus = false): array
    {
        $field = fn (string $name) => $prefix === '' ? $name : $prefix.$name;

        $rules = [
            $field('specie') => $this->petLookupNameRules('Specie'),
            $field('breed') => $this->petLookupNameRules('Breed'),
            $field('sex_id') => ['required', 'integer', Rule::exists('sex', 'sex_id')],
            $field('pet_date_of_birth') => ['required', 'date', 'before_or_equal:today'],
            $field('is_spay_neuter') => ['required', 'boolean'],
            $field('rabies_vaccination_date') => ['nullable', 'date', 'before_or_equal:today'],
        ];

        $rules[$field('pet_status_id')] = $requireStatus
            ? ['required', 'integer', Rule::exists('pet_status', 'pet_status_id')]
            : ['sometimes', 'nullable', 'integer', Rule::exists('pet_status', 'pet_status_id')];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function petCensusMessages(string $prefix = ''): array
    {
        $field = fn (string $name) => $prefix === '' ? $name : $prefix.$name;

        return [
            $field('specie').'.required' => 'Specie is required.',
            $field('breed').'.required' => 'Breed is required.',
            $field('sex_id').'.required' => 'Sex is required.',
            $field('sex_id').'.exists' => 'The selected sex does not exist.',
            $field('pet_date_of_birth').'.required' => 'Pet date of birth is required.',
            $field('pet_date_of_birth').'.before_or_equal' => 'Pet date of birth cannot be in the future.',
            $field('is_spay_neuter').'.required' => 'Spay/neuter is required.',
            $field('rabies_vaccination_date').'.before_or_equal' => 'Rabies vaccination date cannot be in the future.',
            $field('pet_status_id').'.required' => 'Pet status is required.',
            $field('pet_status_id').'.exists' => 'The selected pet status does not exist.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $pets = $this->input('pets');

                if (is_array($pets)) {
                    foreach ($pets as $index => $pet) {
                        if (! is_array($pet)) {
                            continue;
                        }

                        $this->assertRabiesOnOrAfterBirth(
                            $validator,
                            $pet['pet_date_of_birth'] ?? null,
                            $pet['rabies_vaccination_date'] ?? null,
                            'pets.'.$index.'.rabies_vaccination_date',
                        );
                    }

                    return;
                }

                $this->assertRabiesOnOrAfterBirth(
                    $validator,
                    $this->input('pet_date_of_birth'),
                    $this->input('rabies_vaccination_date'),
                    'rabies_vaccination_date',
                );
            },
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function petLookupNameRules(string $label): array
    {
        return [
            'required',
            'string',
            'min:2',
            'max:45',
            function (string $attribute, mixed $value, Closure $fail) use ($label): void {
                if (! is_string($value) || preg_match("/^[\\p{L} '\\-]+$/u", $value) !== 1 || preg_match('/\p{L}/u', $value) !== 1) {
                    $fail("{$label} may only contain letters, spaces, hyphens, and apostrophes.");
                }
            },
        ];
    }

    /**
     * @param  array<string, mixed>  $pet
     * @return array<string, mixed>
     */
    private function normalizePetInput(array $pet): array
    {
        foreach (['specie', 'breed'] as $field) {
            if (array_key_exists($field, $pet) && is_string($pet[$field])) {
                $pet[$field] = trim($pet[$field]);
            }
        }

        if (array_key_exists('rabies_vaccination_date', $pet) && $pet['rabies_vaccination_date'] === '') {
            $pet['rabies_vaccination_date'] = null;
        }

        return $pet;
    }

    private function assertRabiesOnOrAfterBirth(Validator $validator, mixed $birth, mixed $rabies, string $key): void
    {
        if ($rabies === null || $rabies === '' || $birth === null || $birth === '') {
            return;
        }

        try {
            $birthDate = Carbon::parse($birth)->startOfDay();
            $rabiesDate = Carbon::parse($rabies)->startOfDay();
        } catch (\Throwable) {
            return;
        }

        if ($rabiesDate->lt($birthDate)) {
            $validator->errors()->add($key, 'Rabies vaccination date cannot be before the pet date of birth.');
        }
    }
}
