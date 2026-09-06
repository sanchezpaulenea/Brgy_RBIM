<?php

namespace App\Http\Requests\ResidentManagement\Demographics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexResidentRequest extends FormRequest
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
            'search' => ['sometimes', 'string', 'max:45'],
            'household_id' => ['sometimes', 'integer', Rule::exists('household', 'household_id')],
            'sex_id' => ['sometimes', 'integer', Rule::exists('sex', 'sex_id')],
            'resident_type_id' => [
                'sometimes',
                'integer',
                Rule::exists('resident_type', 'resident_type_id'),
            ],
            'resident_status_id' => [
                'sometimes',
                'integer',
                Rule::exists('resident_status', 'resident_status_id'),
            ],
            'age_min' => ['sometimes', 'integer', 'min:0', 'max:150'],
            'age_max' => ['sometimes', 'integer', 'min:0', 'max:150', 'gte:age_min'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'household_id.exists' => 'The selected household does not exist.',
            'sex_id.exists' => 'The selected sex does not exist.',
            'resident_type_id.exists' => 'The selected resident type does not exist.',
            'resident_status_id.exists' => 'The selected resident status does not exist.',
            'age_max.gte' => 'Maximum age must be greater than or equal to minimum age.',
        ];
    }

    /**
     * @return array{
     *     search?: string,
     *     household_id?: int,
     *     sex_id?: int,
     *     resident_type_id?: int,
     *     resident_status_id?: int,
     *     age_min?: int,
     *     age_max?: int
     * }
     */
    public function filters(): array
    {
        return $this->validated();
    }
}
