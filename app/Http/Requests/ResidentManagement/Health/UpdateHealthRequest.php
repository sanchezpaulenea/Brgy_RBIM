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
            'health_insurance_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('health_insurance', 'health_insurance_id'),
            ],
            'facility_visited_past_12mos_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('facility_visited_past_12mos', 'facility_visited_past_12mos_id'),
            ],
            'facility_visit_reason_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('facility_visit_reason', 'facility_visit_reason_id'),
            ],
            'disability' => ['sometimes', 'nullable', 'string', 'max:45'],
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
                    'disability',
                ], 'health', 'Provide at least one health field to update.');
            },
        ];
    }
}
