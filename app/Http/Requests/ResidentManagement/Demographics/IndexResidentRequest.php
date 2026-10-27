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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'slim' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'household_id.exists' => 'The selected household does not exist.',
            'sex_id.exists' => 'The selected gender does not exist.',
            'resident_type_id.exists' => 'The selected resident type does not exist.',
            'resident_status_id.exists' => 'The selected resident status does not exist.',
            'age_max.gte' => 'Maximum age must be greater than or equal to minimum age.',
            'page.integer' => 'Page must be a whole number.',
            'page.min' => 'Page must be at least 1.',
            'per_page.integer' => 'Per page must be a whole number.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page may not be greater than 100.',
            'slim.boolean' => 'Slim must be true or false.',
        ];
    }

    public function isPaged(): bool
    {
        return array_key_exists('page', $this->validated());
    }

    public function pageNumber(): int
    {
        return (int) $this->validated('page');
    }

    public function perPage(): int
    {
        $perPage = $this->validated('per_page');

        return $perPage === null ? 50 : (int) $perPage;
    }

    public function wantsSlimNames(): bool
    {
        return $this->boolean('slim');
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
        $filters = $this->validated();
        unset($filters['page'], $filters['per_page'], $filters['slim']);

        return $filters;
    }
}
