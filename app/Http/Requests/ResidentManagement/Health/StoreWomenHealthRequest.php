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
            'number_pregnancies' => ['required', 'integer', 'min:0'],
            'living_children' => ['required', 'integer', 'min:0'],
            'family_planning_method_id' => $this->optionalLookupRule(
                'family_planning_method',
                'family_planning_method_id',
            ),
            'source_of_fp_method_id' => $this->optionalLookupRule(
                'source_of_fp_method',
                'source_of_fp_method_id',
            ),
            'have_intention_to_use_fp' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'number_pregnancies.required' => 'Number of pregnancies is required.',
            'number_pregnancies.integer' => 'Number of pregnancies must be numeric.',
            'living_children.required' => 'Living children is required.',
            'living_children.integer' => 'Living children must be numeric.',
            'family_planning_method_id.exists' => 'The selected family planning method does not exist.',
            'source_of_fp_method_id.exists' => 'The selected source of family planning method does not exist.',
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
