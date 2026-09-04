<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Models\HouseholdManagement\CensusStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHouseholdAssessmentStatusRequest extends FormRequest
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
            'census_status_id' => [
                'required',
                'integer',
                Rule::exists('census_status', 'census_status_id'),
                Rule::notIn([CensusStatus::CALLBACK]),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'census_status_id.required' => 'Census status is required.',
            'census_status_id.exists' => 'The selected census status does not exist.',
            'census_status_id.not_in' => 'Choose Completed or Refused. Status can only be updated while the latest assessment is still CB (Callback).',
        ];
    }
}
