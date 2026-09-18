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
        $this->mergeTitleCased(['disability']);

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
            'health_insurance_id.exists' => 'The selected health insurance does not exist.',
            'facility_visited_past_12mos_id.exists' => 'The selected facility does not exist.',
            'facility_visit_reason_id.exists' => 'The selected visit reason does not exist.',
            'disability_id.required_without' => 'Disability is required.',
            'disability.required_without' => 'Disability is required.',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function optionalLookupRule(string $table, string $column): array
    {
        return [
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
