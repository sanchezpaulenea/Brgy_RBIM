<?php

namespace App\Http\Requests\HouseholdManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPetCensusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'household_id' => ['sometimes', 'integer', Rule::exists('household', 'household_id')],
            'specie_id' => ['sometimes', 'integer', Rule::exists('specie', 'specie_id')],
            'breed_id' => ['sometimes', 'integer', Rule::exists('breed', 'breed_id')],
            'sex_id' => ['sometimes', 'integer', Rule::exists('sex', 'sex_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'household_id.exists' => 'The selected household does not exist.',
            'specie_id.exists' => 'The selected species does not exist.',
            'breed_id.exists' => 'The selected breed does not exist.',
            'sex_id.exists' => 'The selected sex does not exist.',
        ];
    }

    /**
     * @return array{household_id?: int, specie_id?: int, breed_id?: int, sex_id?: int}
     */
    public function filters(): array
    {
        return $this->validated();
    }
}
