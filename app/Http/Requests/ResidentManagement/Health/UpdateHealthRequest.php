<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Rules\ValidPwdIdNumber;
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
        $this->mergeTitleCased([
            'disability',
            'health_insurance',
            'facility_visited_past_12mos',
            'facility_visit_reason',
        ]);

        if ($this->exists('pwd_id_number')) {
            $this->merge(['pwd_id_number' => ValidPwdIdNumber::normalize($this->input('pwd_id_number'))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'health_insurance_id' => [
                'sometimes',
                'required_without:health_insurance',
                'nullable',
                'integer',
                Rule::exists('health_insurance', 'health_insurance_id'),
            ],
            'health_insurance' => ['sometimes', 'required_without:health_insurance_id', 'nullable', 'string', 'max:45'],
            'facility_visited_past_12mos_id' => [
                'sometimes',
                'required_without:facility_visited_past_12mos',
                'nullable',
                'integer',
                Rule::exists('facility_visited_past_12mos', 'facility_visited_past_12mos_id'),
            ],
            'facility_visited_past_12mos' => ['sometimes', 'required_without:facility_visited_past_12mos_id', 'nullable', 'string', 'max:45'],
            'facility_visit_reason_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('facility_visit_reason', 'facility_visit_reason_id'),
            ],
            'facility_visit_reason' => ['sometimes', 'nullable', 'string', 'max:45'],
            'disability_id' => [
                'sometimes',
                'required_without:disability',
                'nullable',
                'integer',
                Rule::exists('disability', 'disability_id'),
            ],
            'disability' => ['sometimes', 'required_without:disability_id', 'nullable', 'string', 'max:45'],
            'pwd_id_number' => ['sometimes', 'nullable', 'string', new ValidPwdIdNumber],
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
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'health_insurance_id',
                    'health_insurance',
                    'facility_visited_past_12mos_id',
                    'facility_visited_past_12mos',
                    'facility_visit_reason_id',
                    'facility_visit_reason',
                    'disability_id',
                    'disability',
                    'pwd_id_number',
                ], 'health', 'Provide at least one health field to update.');
            },
        ];
    }
}
