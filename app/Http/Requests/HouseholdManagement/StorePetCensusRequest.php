<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\Concerns\ValidatesPetCensus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePetCensusRequest extends FormRequest
{
    use ValidatesPetCensus;

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
            'household_id' => ['required', 'integer', Rule::exists('household', 'household_id')],
            'pets' => ['required', 'array', 'min:1', 'max:'.self::MAX_PETS_PER_REQUEST],
            ...$this->petCensusRules('pets.*.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'household_id.required' => 'Household is required.',
            'household_id.exists' => 'The selected household does not exist.',
            'pets.required' => 'Add at least one pet.',
            'pets.min' => 'Add at least one pet.',
            'pets.max' => 'You can add up to '.self::MAX_PETS_PER_REQUEST.' pets at a time.',
            ...$this->petCensusMessages('pets.*.'),
        ];
    }
}
