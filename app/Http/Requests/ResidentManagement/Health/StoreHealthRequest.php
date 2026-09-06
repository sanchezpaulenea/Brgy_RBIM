<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Http\Requests\Concerns\TitleCasesAttributes;
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
        ];
    }
}
