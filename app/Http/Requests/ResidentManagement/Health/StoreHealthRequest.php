<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Rules\ValidPwdIdNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHealthRequest extends FormRequest
{
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
                'required_without:health_insurance',
                'nullable',
                'integer',
                Rule::exists('health_insurance', 'health_insurance_id'),
            ],
            'health_insurance' => ['required_without:health_insurance_id', 'nullable', 'string', 'max:45'],
            'facility_visited_past_12mos_id' => [
                'required_without:facility_visited_past_12mos',
                'nullable',
                'integer',
                Rule::exists('facility_visited_past_12mos', 'facility_visited_past_12mos_id'),
            ],
            'facility_visited_past_12mos' => ['required_without:facility_visited_past_12mos_id', 'nullable', 'string', 'max:45'],
            'facility_visit_reason_id' => [
                'nullable',
                'integer',
                Rule::exists('facility_visit_reason', 'facility_visit_reason_id'),
            ],
            'facility_visit_reason' => ['nullable', 'string', 'max:45'],
            'disability_id' => [
                'required_without:disability',
                'nullable',
                'integer',
                Rule::exists('disability', 'disability_id'),
            ],
            'disability' => ['required_without:disability_id', 'nullable', 'string', 'max:45'],
            'pwd_id_number' => ['nullable', 'string', new ValidPwdIdNumber],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'health_insurance_id.required_without' => 'Health insurance is required.',
            'health_insurance.required_without' => 'Health insurance is required.',
            'facility_visited_past_12mos_id.required_without' => 'Facility visited past 12 months is required.',
            'facility_visited_past_12mos.required_without' => 'Facility visited past 12 months is required.',
            'health_insurance_id.exists' => 'The selected health insurance does not exist.',
            'facility_visited_past_12mos_id.exists' => 'The selected facility does not exist.',
            'facility_visit_reason_id.exists' => 'The selected visit reason does not exist.',
            'disability_id.required_without' => 'Disability is required.',
            'disability.required_without' => 'Disability is required.',
        ];
    }
}
