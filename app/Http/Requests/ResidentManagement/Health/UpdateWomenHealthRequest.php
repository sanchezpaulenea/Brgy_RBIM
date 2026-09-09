<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateWomenHealthRequest extends FormRequest
{
    use RequiresAtLeastOneField;

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
            'number_pregnancies' => ['sometimes', 'required', 'integer', 'min:0'],
            'living_children' => ['sometimes', 'required', 'integer', 'min:0'],
            'family_planning_method_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('family_planning_method', 'family_planning_method_id'),
            ],
            'source_of_fp_method_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('source_of_fp_method', 'source_of_fp_method_id'),
            ],
            'have_intention_to_use_fp' => ['sometimes', 'required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'number_pregnancies',
                    'living_children',
                    'family_planning_method_id',
                    'source_of_fp_method_id',
                    'have_intention_to_use_fp',
                ], 'women_health', 'Provide at least one women\'s health field to update.');
            },
        ];
    }
}
