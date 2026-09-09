<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Models\ResidentManagement\Health\Health;
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
        $this->mergeTitleCased(['disability']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'health_insurance_id' => ['required', 'integer', Rule::exists('health_insurance', 'health_insurance_id')],
            'facility_visited_past_12mos_id' => [
                'required',
                'integer',
                Rule::exists('facility_visited_past_12mos', 'facility_visited_past_12mos_id'),
            ],
            'facility_visit_reason_id' => [
                'required',
                'integer',
                Rule::exists('facility_visit_reason', 'facility_visit_reason_id'),
            ],
            'disability' => ['nullable', 'string', 'max:45'],
            'pwd_id_number' => [
                Rule::excludeIf(fn () => ! Health::indicatesDisability($this->input('disability'))),
                'required',
                'integer',
                'min:1',
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
            'health_insurance_id.exists' => 'The selected health insurance does not exist.',
            'facility_visited_past_12mos_id.exists' => 'The selected facility does not exist.',
            'facility_visit_reason_id.exists' => 'The selected visit reason does not exist.',
            'pwd_id_number.required' => 'PWD ID number is required when a disability is recorded.',
            'pwd_id_number.integer' => 'PWD ID number must be numeric.',
            'pwd_id_number.min' => 'PWD ID number must be a positive number.',
        ];
    }
}
