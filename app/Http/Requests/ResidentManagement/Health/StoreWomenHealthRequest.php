<?php

namespace App\Http\Requests\ResidentManagement\Health;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWomenHealthRequest extends FormRequest
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
            'living_children' => ['required', 'integer', 'min:0'],
            'family_planning_method_id' => [
                'required',
                'integer',
                Rule::exists('family_planning_method', 'family_planning_method_id'),
            ],
            'source_of_fp_method_id' => [
                'required',
                'integer',
                Rule::exists('source_of_fp_method', 'source_of_fp_method_id'),
            ],
            'have_intention_to_use_fp' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'living_children.integer' => 'Living children must be numeric.',
            'family_planning_method_id.exists' => 'The selected family planning method does not exist.',
            'source_of_fp_method_id.exists' => 'The selected source of family planning method does not exist.',
        ];
    }
}
