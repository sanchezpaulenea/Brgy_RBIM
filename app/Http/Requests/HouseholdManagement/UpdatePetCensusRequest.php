<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\Concerns\ValidatesPetCensus;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePetCensusRequest extends FormRequest
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
        return $this->petCensusRules(requireStatus: true);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->petCensusMessages();
    }
}
