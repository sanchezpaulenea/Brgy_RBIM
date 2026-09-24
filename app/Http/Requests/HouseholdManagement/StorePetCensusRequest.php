<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\Concerns\ValidatesPetCensus;
use Illuminate\Foundation\Http\FormRequest;

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
            'pets' => ['required', 'array', 'min:1'],
            ...$this->petCensusRules('pets.*.'),
        ];
    }
}
