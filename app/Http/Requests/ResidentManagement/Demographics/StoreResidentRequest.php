<?php

namespace App\Http\Requests\ResidentManagement\Demographics;

use App\Models\ResidentManagement\Demographic\RelationshipToHouseholdHead;
use App\Rules\ValidPersonnelName;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'last_name' => $this->titleCaseName($this->input('last_name')),
            'first_name' => $this->titleCaseName($this->input('first_name')),
            'middle_name' => $this->titleCaseName($this->input('middle_name')),
            'suffix' => $this->titleCaseName($this->input('suffix')),
            'birth_city_municipality' => $this->titleCasePlace($this->input('birth_city_municipality')),
            'birth_province' => $this->titleCasePlace($this->input('birth_province')),
            'birth_country' => $this->titleCasePlace($this->input('birth_country')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:45', new ValidPersonnelName],
            'first_name' => ['required', 'string', 'max:45', new ValidPersonnelName],
            'middle_name' => ['nullable', 'string', 'max:45', new ValidPersonnelName],
            'suffix' => ['nullable', 'string', 'max:45', new ValidPersonnelName],
            'relationship_to_hh_id' => [
                'required',
                'integer',
                Rule::exists('relationship_to_hh', 'relationship_to_hh_id'),
                Rule::notIn([RelationshipToHouseholdHead::HEAD]),
            ],
            'sex_id' => ['required', 'integer', Rule::exists('sex', 'sex_id')],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'birth_city_municipality' => ['required', 'string', 'max:45', new ValidPlaceName('City / municipality of birth')],
            'birth_province' => ['required', 'string', 'max:45', new ValidPlaceName('Province of birth')],
            'birth_country' => ['required', 'string', 'max:45', new ValidPlaceName('Country of birth')],
            'nationality_id' => ['required', 'integer', Rule::exists('nationality', 'nationality_id')],
            'religion_id' => ['required', 'integer', Rule::exists('religion', 'religion_id')],
            'ethnicity_id' => ['required', 'integer', Rule::exists('ethnicity', 'ethnicity_id')],
            'marital_status_id' => ['required', 'integer', Rule::exists('marital_status', 'marital_status_id')],
            'resident_type_id' => ['required', 'integer', Rule::exists('resident_type', 'resident_type_id')],
            'clan_id' => ['sometimes', 'integer', Rule::exists('clan', 'clan_id')],
            'resident_status_id' => [
                'sometimes',
                'integer',
                Rule::exists('resident_status', 'resident_status_id'),
            ],
            'household_id' => ['required', 'integer', Rule::exists('household', 'household_id')],
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
            'relationship_to_hh_id.required' => 'Relationship to household head is required.',
            'relationship_to_hh_id.exists' => 'The selected relationship does not exist.',
            'relationship_to_hh_id.not_in' => 'Additional residents cannot be registered as the household head. Register a new household instead.',
            'sex_id.required' => 'Sex is required.',
            'sex_id.exists' => 'The selected sex does not exist.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
            'birth_city_municipality.required' => 'City / municipality of birth is required.',
            'birth_province.required' => 'Province of birth is required.',
            'birth_country.required' => 'Country of birth is required.',
            'nationality_id.required' => 'Nationality is required.',
            'nationality_id.exists' => 'The selected nationality does not exist.',
            'religion_id.required' => 'Religion is required.',
            'religion_id.exists' => 'The selected religion does not exist.',
            'ethnicity_id.required' => 'Ethnicity is required.',
            'ethnicity_id.exists' => 'The selected ethnicity does not exist.',
            'marital_status_id.required' => 'Marital status is required.',
            'marital_status_id.exists' => 'The selected marital status does not exist.',
            'resident_type_id.required' => 'Resident type is required.',
            'resident_type_id.exists' => 'The selected resident type does not exist.',
            'clan_id.exists' => 'The selected clan does not exist.',
            'resident_status_id.exists' => 'The selected resident status does not exist.',
            'household_id.required' => 'Household is required.',
            'household_id.exists' => 'The selected household does not exist.',
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
