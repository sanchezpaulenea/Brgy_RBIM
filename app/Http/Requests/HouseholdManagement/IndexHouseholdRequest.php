<?php

namespace App\Http\Requests\HouseholdManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexHouseholdRequest extends FormRequest
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
            'street_id' => ['sometimes', 'integer', Rule::exists('street', 'street_id')],
            'household_status_id' => [
                'sometimes',
                'integer',
                Rule::exists('household_status', 'household_status_id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'street_id.exists' => 'The selected street does not exist.',
            'household_status_id.exists' => 'The selected household status does not exist.',
        ];
    }

    /**
     * @return array{street_id?: int, household_status_id?: int}
     */
    public function filters(): array
    {
        return $this->validated();
    }
}
