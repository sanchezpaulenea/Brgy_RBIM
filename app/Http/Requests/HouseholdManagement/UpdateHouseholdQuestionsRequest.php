<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\Concerns\ValidatesHouseholdQuestions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateHouseholdQuestionsRequest extends FormRequest
{
    use ValidatesHouseholdQuestions;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareHouseholdQuestionInput();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->householdQuestionRules(false);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->householdQuestionMessages();
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return $this->requireAtLeastOneQuestionField([
            'ownership_of_housing_unit_id',
            'ownership_of_lot_id',
            'fuel_type_for_lighting_id',
            'fuel_type_for_cooking_id',
            'main_source_drinking_water_id',
            'kitchen_garbage_disposal_id',
            'perform_garbage_seggragation',
            'toilet_facility_type_id',
            'type_of_building_house_id',
            'construction_material_outer_wall_id',
            'female_hhm_died_past_12mos',
            'child_hhm_died_past_12mos',
            'common_diseases',
            'primary_needs',
            'intend_to_stay_brgy',
            'intend_to_stay_municipality',
            'intend_to_stay_province',
        ]);
    }
}
