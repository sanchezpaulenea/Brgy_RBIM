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
            'household_id' => ['sometimes', 'integer', Rule::exists('household', 'household_id')],
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'household_id.exists' => 'The selected household does not exist.',
            'resident_type_id.exists' => 'The selected resident type does not exist.',
            'resident_status_id.exists' => 'The selected resident status does not exist.',
        ];
    }

    /**
     * @return array{household_id?: int, resident_type_id?: int, resident_status_id?: int}
     */
    public function filters(): array
    {
        return $this->validated();
    }
}
