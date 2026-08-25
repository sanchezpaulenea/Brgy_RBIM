<?php

namespace App\Http\Requests\ResidentManagement\Demographics;

use App\Models\ResidentManagement\Demographic\RelationshipToHouseholdHead;
use App\Rules\ValidPersonnelName;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['last_name', 'first_name', 'middle_name', 'suffix'] as $field) {
            if ($this->exists($field)) {
                $merge[$field] = $this->titleCaseName($this->input($field));
            }
        }

        foreach (['birth_city_municipality', 'birth_province', 'birth_country'] as $field) {
            if ($this->exists($field)) {
                $merge[$field] = $this->titleCasePlace($this->input($field));
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'last_name' => ['sometimes', 'required', 'string', 'max:45', new ValidPersonnelName],
            'first_name' => ['sometimes', 'required', 'string', 'max:45', new ValidPersonnelName],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:45', new ValidPersonnelName],
            'suffix' => ['sometimes', 'nullable', 'string', 'max:45', new ValidPersonnelName],
            'relationship_to_hh_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('relationship_to_hh', 'relationship_to_hh_id'),
                Rule::notIn([RelationshipToHouseholdHead::HEAD]),
            ],
            'sex_id' => ['sometimes', 'required', 'integer', Rule::exists('sex', 'sex_id')],
            'date_of_birth' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'birth_city_municipality' => [
                'sometimes',
                'required',
                'string',
                'max:45',
                new ValidPlaceName('City / municipality of birth'),
            ],
            'birth_province' => ['sometimes', 'required', 'string', 'max:45', new ValidPlaceName('Province of birth')],
            'birth_country' => ['sometimes', 'required', 'string', 'max:45', new ValidPlaceName('Country of birth')],
            'nationality_id' => ['sometimes', 'required', 'integer', Rule::exists('nationality', 'nationality_id')],
            'religion_id' => ['sometimes', 'required', 'integer', Rule::exists('religion', 'religion_id')],
            'ethnicity_id' => ['sometimes', 'required', 'integer', Rule::exists('ethnicity', 'ethnicity_id')],
            'marital_status_id' => ['sometimes', 'required', 'integer', Rule::exists('marital_status', 'marital_status_id')],
            'resident_type_id' => ['sometimes', 'required', 'integer', Rule::exists('resident_type', 'resident_type_id')],
            'clan_id' => ['sometimes', 'required', 'integer', Rule::exists('clan', 'clan_id')],
            'resident_status_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('resident_status', 'resident_status_id'),
            ],
            'household_id' => ['sometimes', 'required', 'integer', Rule::exists('household', 'household_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'last_name.required' => 'Last name is required.',
            'first_name.required' => 'First name is required.',
            'relationship_to_hh_id.exists' => 'The selected relationship does not exist.',
            'relationship_to_hh_id.not_in' => 'A resident cannot be changed to household head on this update.',
            'sex_id.exists' => 'The selected sex does not exist.',
            'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
            'nationality_id.exists' => 'The selected nationality does not exist.',
            'religion_id.exists' => 'The selected religion does not exist.',
            'ethnicity_id.exists' => 'The selected ethnicity does not exist.',
            'marital_status_id.exists' => 'The selected marital status does not exist.',
            'resident_type_id.exists' => 'The selected resident type does not exist.',
            'clan_id.exists' => 'The selected clan does not exist.',
            'resident_status_id.exists' => 'The selected resident status does not exist.',
            'household_id.exists' => 'The selected household does not exist.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $updatable = [
                    'last_name',
                    'first_name',
                    'middle_name',
                    'suffix',
                    'relationship_to_hh_id',
                    'sex_id',
                    'date_of_birth',
                    'birth_city_municipality',
                    'birth_province',
                    'birth_country',
                    'nationality_id',
                    'religion_id',
                    'ethnicity_id',
                    'marital_status_id',
                    'resident_type_id',
                    'clan_id',
                    'resident_status_id',
                    'household_id',
                ];

                foreach ($updatable as $field) {
                    if ($this->exists($field)) {
                        return;
                    }
                }

                $validator->errors()->add(
                    'resident',
                    'Provide at least one resident field to update.',
                );
            },
        ];
    }

    private function titleCaseName(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $formatted = Str::of($value)->squish()->title()->toString();

        return $formatted === '' ? null : $formatted;
    }

    private function titleCasePlace(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $formatted = Str::of($value)->squish()->title()->toString();

        return $formatted === '' ? null : $formatted;
    }
}
