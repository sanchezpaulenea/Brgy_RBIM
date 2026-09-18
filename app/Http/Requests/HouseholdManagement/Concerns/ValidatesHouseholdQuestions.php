<?php

namespace App\Http\Requests\HouseholdManagement\Concerns;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Rules\ValidPlaceName;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesHouseholdQuestions
{
    use TitleCasesAttributes;

    protected function prepareHouseholdQuestionInput(): void
    {
        $this->mergeTitleCased([
            'intend_to_stay_brgy',
            'intend_to_stay_municipality',
            'intend_to_stay_province',
        ]);

        if ($this->exists('common_diseases')) {
            $this->merge([
                'common_diseases' => $this->normalizedNamedList($this->input('common_diseases')),
            ]);
        }

        if ($this->exists('primary_needs')) {
            $this->merge([
                'primary_needs' => $this->normalizedNamedList($this->input('primary_needs')),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function householdQuestionRules(bool $required): array
    {
        $presence = $required ? 'required' : 'sometimes';

        return [
            'ownership_of_housing_unit_id' => [
                $presence,
                'integer',
                Rule::exists('ownership_type', 'ownership_type_id'),
            ],
            'ownership_of_lot_id' => [
                $presence,
                'integer',
                Rule::exists('ownership_type', 'ownership_type_id'),
            ],
            'fuel_type_for_lighting_id' => [
                $presence,
                'integer',
                Rule::exists('fuel_type', 'fuel_type_id'),
            ],
            'fuel_type_for_cooking_id' => [
                $presence,
                'integer',
                Rule::exists('fuel_type', 'fuel_type_id'),
            ],
            'main_source_drinking_water_id' => [
                $presence,
                'integer',
                Rule::exists('water_source', 'water_source'),
            ],
            'kitchen_garbage_disposal_id' => [
                $presence,
                'integer',
                Rule::exists('kitchen_garbage_disposal', 'kitchen_garbage_disposal_id'),
            ],
            'perform_garbage_seggragation' => [$presence, 'boolean'],
            'toilet_facility_type_id' => [
                $presence,
                'integer',
                Rule::exists('toilet_facility_type', 'toilet_facility_type_id'),
            ],
            'type_of_building_house_id' => [
                $presence,
                'integer',
                Rule::exists('building_house_type', 'building_house_type_id'),
            ],
            'construction_material_outer_wall_id' => [
                $presence,
                'integer',
                Rule::exists('construction_material_outer_wall', 'construction_material_outer_wall_id'),
            ],
            'female_hhm_died_past_12mos' => [$presence, 'boolean'],
            'child_hhm_died_past_12mos' => [$presence, 'boolean'],
            'common_diseases' => ['sometimes', 'array', 'max:3'],
            'common_diseases.*' => ['nullable', 'string', 'max:45'],
            'primary_needs' => ['sometimes', 'array', 'max:3'],
            'primary_needs.*' => ['nullable', 'string', 'max:45'],
            'intend_to_stay_brgy' => [
                $presence,
                'string',
                'max:45',
                new ValidPlaceName('Intended barangay of stay'),
            ],
            'intend_to_stay_municipality' => [
                $presence,
                'string',
                'max:45',
                new ValidPlaceName('Intended municipality of stay'),
            ],
            'intend_to_stay_province' => [
                $presence,
                'string',
                'max:45',
                new ValidPlaceName('Intended province of stay'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function householdQuestionMessages(): array
    {
        return [
            'ownership_of_housing_unit_id.required' => 'Ownership of the housing unit is required.',
            'ownership_of_lot_id.required' => 'Ownership of the lot is required.',
            'fuel_type_for_lighting_id.required' => 'Fuel used for lighting is required.',
            'fuel_type_for_cooking_id.required' => 'Fuel used for cooking is required.',
            'main_source_drinking_water_id.required' => 'Main source of drinking water is required.',
            'kitchen_garbage_disposal_id.required' => 'Kitchen garbage disposal is required.',
            'perform_garbage_seggragation.required' => 'Please indicate whether the household segregates garbage.',
            'toilet_facility_type_id.required' => 'Type of toilet facility is required.',
            'type_of_building_house_id.required' => 'Type of building/house is required.',
            'construction_material_outer_wall_id.required' => 'Construction material of the outer wall is required.',
            'female_hhm_died_past_12mos.required' => 'Please indicate whether a female household member died in the past 12 months.',
            'child_hhm_died_past_12mos.required' => 'Please indicate whether a child household member below 5 years old died in the past 12 months.',
            'intend_to_stay_brgy.required' => 'Intended barangay of stay is required.',
            'intend_to_stay_municipality.required' => 'Intended municipality of stay is required.',
            'intend_to_stay_province.required' => 'Intended province of stay is required.',
            'common_diseases.max' => 'List up to three common diseases.',
            'primary_needs.max' => 'List up to three primary needs.',
        ];
    }

    /**
     * @param  list<string>  $fields
     * @return array<int, callable(Validator): void>
     */
    protected function requireAtLeastOneQuestionField(array $fields): array
    {
        return [
            function (Validator $validator) use ($fields): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                foreach ($fields as $field) {
                    if ($this->exists($field)) {
                        return;
                    }
                }

                $validator->errors()->add(
                    'questions',
                    'Provide at least one household question field to update.',
                );
            },
        ];
    }

    /**
     * @return list<string>
     */
    private function normalizedNamedList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        $normalized = [];

        foreach ($values as $value) {
            $formatted = $this->titleCaseValue($value);

            if (! is_string($formatted) || $formatted === '') {
                continue;
            }

            $normalized[] = $formatted;
        }

        return array_values(array_unique($normalized));
    }
}
