<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Models\ResidentManagement\Health\FamilyPlanningMethod;
use App\Models\ResidentManagement\Health\WomenHealth;
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
            'family_planning_method_id' => $this->optionalLookupRule(
                'family_planning_method',
                'family_planning_method_id',
            ),
            'source_of_fp_method_id' => [
                'sometimes',
                'nullable',
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

                if ($validator->errors()->isNotEmpty() || $this->familyPlanningMethodIndicatesNone()) {
                    return;
                }

                $source = $this->input('source_of_fp_method_id');
                $sourceBlank = ! $this->exists('source_of_fp_method_id')
                    || $source === null
                    || $source === ''
                    || (int) $source <= 0;

                if ($this->exists('family_planning_method_id') && $sourceBlank) {
                    $validator->errors()->add(
                        'source_of_fp_method_id',
                        'Source of family planning method is required.',
                    );
                }

                if ($this->exists('source_of_fp_method_id') && $sourceBlank && ! $this->exists('family_planning_method_id')) {
                    $validator->errors()->add(
                        'source_of_fp_method_id',
                        'Source of family planning method is required.',
                    );
                }
            },
        ];
    }

    private function familyPlanningMethodIndicatesNone(): bool
    {
        $methodId = $this->exists('family_planning_method_id')
            ? (int) $this->input('family_planning_method_id')
            : (int) ($this->route('womenHealth') instanceof WomenHealth
                ? $this->route('womenHealth')->family_planning_method_id
                : 0);

        if ($methodId <= 0) {
            return false;
        }

        $method = FamilyPlanningMethod::query()
            ->where('family_planning_method_id', $methodId)
            ->first();

        return $method?->indicatesNone() ?? false;
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
