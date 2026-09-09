<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateHealthRequest extends FormRequest
{
    use RequiresAtLeastOneField;
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased(['disability']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'health_insurance_id' => $this->optionalLookupRule('health_insurance', 'health_insurance_id'),
            'facility_visited_past_12mos_id' => $this->optionalLookupRule(
                'facility_visited_past_12mos',
                'facility_visited_past_12mos_id',
            ),
            'facility_visit_reason_id' => $this->optionalLookupRule(
                'facility_visit_reason',
                'facility_visit_reason_id',
            ),
            'disability_id' => [
                'sometimes',
                'required_without:disability',
                'nullable',
                'integer',
                Rule::exists('disability', 'disability_id'),
            ],
            'disability' => ['sometimes', 'required_without:disability_id', 'nullable', 'string', 'max:45'],
            'pwd_id_number' => [
                'sometimes',
                'nullable',
                'integer',
                'min:0',
                'max:2147483647',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'disability_id.required_without' => 'Disability is required.',
            'disability.required_without' => 'Disability is required.',
            'pwd_id_number.integer' => 'PWD ID number must be numeric.',
            'pwd_id_number.min' => 'PWD ID number must be a whole number of 0 or greater.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'health_insurance_id',
                    'facility_visited_past_12mos_id',
                    'facility_visit_reason_id',
                    'disability_id',
                    'disability',
                    'pwd_id_number',
                ], 'health', 'Provide at least one health field to update.');
            },
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function optionalLookupRule(string $table, string $column): array
    {
        return [
            'sometimes',
            'nullable',
            'integer',
            'min:0',
            Rule::when(
                fn () => (int) $this->input($column) > 0,
                [Rule::exists($table, $column)],
            ),
        ];
    }
}
