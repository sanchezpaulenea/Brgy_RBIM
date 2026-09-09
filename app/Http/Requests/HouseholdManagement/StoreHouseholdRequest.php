<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\Concerns\NormalizesHouseholdAddress;
use App\Rules\ValidPersonnelName;
use App\Rules\ValidPlaceName;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Throwable;

class StoreHouseholdRequest extends FormRequest
{
    use NormalizesHouseholdAddress;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeNormalizedAddressFields([
            'house_lot',
            'block_num',
            'building_name',
            'unit_num',
        ]);

        $head = $this->input('head');

        if (! is_array($head)) {
            return;
        }

        $this->merge([
            'head' => [
                ...$head,
                'last_name' => $this->titleCaseName($head['last_name'] ?? null),
                'first_name' => $this->titleCaseName($head['first_name'] ?? null),
                'middle_name' => $this->titleCaseName($head['middle_name'] ?? null),
                'suffix' => $this->titleCaseName($head['suffix'] ?? null),
                'birth_city_municipality' => $this->titleCasePlace($head['birth_city_municipality'] ?? null),
                'birth_province' => $this->titleCasePlace($head['birth_province'] ?? null),
                'birth_country' => $this->titleCasePlace($head['birth_country'] ?? null),
            ],
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'clan_id' => ['required', 'integer', Rule::exists('clan', 'clan_id')],
            'street_id' => ['required', 'integer', Rule::exists('street', 'street_id')],
            'house_lot' => ['nullable', 'string', 'max:45'],
            'block_num' => ['nullable', 'string', 'max:45'],
            'building_name' => ['nullable', 'string', 'max:45'],
            'unit_num' => ['nullable', 'string', 'max:45'],
            'household_status_id' => [
                'sometimes',
                'integer',
                Rule::exists('household_status', 'household_status_id'),
            ],
            'head' => ['required', 'array'],
            'head.last_name' => ['required', 'string', 'max:45', new ValidPersonnelName],
            'head.first_name' => ['required', 'string', 'max:45', new ValidPersonnelName],
            'head.middle_name' => ['nullable', 'string', 'max:45', new ValidPersonnelName],
            'head.suffix' => ['nullable', 'string', 'max:45', new ValidPersonnelName],
            'head.sex_id' => ['required', 'integer', Rule::exists('sex', 'sex_id')],
            'head.date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'head.birth_city_municipality' => ['required', 'string', 'max:45', new ValidPlaceName('City / municipality of birth')],
            'head.birth_province' => ['required', 'string', 'max:45', new ValidPlaceName('Province of birth')],
            'head.birth_country' => ['required', 'string', 'max:45', new ValidPlaceName('Country of birth')],
            'head.nationality_id' => ['required', 'integer', Rule::exists('nationality', 'nationality_id')],
            'head.religion_id' => ['required', 'integer', Rule::exists('religion', 'religion_id')],
            'head.ethnicity_id' => ['required', 'integer', Rule::exists('ethnicity', 'ethnicity_id')],
            'head.marital_status_id' => ['required', 'integer', Rule::exists('marital_status', 'marital_status_id')],
            'head.clan_id' => ['sometimes', 'integer', Rule::exists('clan', 'clan_id')],
            'head.resident_status_id' => [
                'sometimes',
                'integer',
                Rule::exists('resident_status', 'resident_status_id'),
            ],
            'head.relationship_to_hh_id' => [
                'sometimes',
                'integer',
                Rule::exists('relationship_to_hh', 'relationship_to_hh_id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clan_id.required' => 'Clan is required.',
            'clan_id.exists' => 'The selected clan does not exist.',
            'street_id.required' => 'Street is required.',
            'street_id.exists' => 'The selected street does not exist.',
            'household_status_id.exists' => 'The selected household status does not exist.',
            'head.required' => 'Head resident information is required.',
            'head.last_name.required' => 'Head last name is required.',
            'head.first_name.required' => 'Head first name is required.',
            'head.sex_id.required' => 'Head sex is required.',
            'head.sex_id.exists' => 'The selected sex does not exist.',
            'head.date_of_birth.required' => 'Head date of birth is required.',
            'head.date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
            'head.birth_city_municipality.required' => 'City / municipality of birth is required.',
            'head.birth_province.required' => 'Province of birth is required.',
            'head.birth_country.required' => 'Country of birth is required.',
            'head.nationality_id.required' => 'Nationality is required.',
            'head.nationality_id.exists' => 'The selected nationality does not exist.',
            'head.religion_id.required' => 'Religion is required.',
            'head.religion_id.exists' => 'The selected religion does not exist.',
            'head.ethnicity_id.required' => 'Ethnicity is required.',
            'head.ethnicity_id.exists' => 'The selected ethnicity does not exist.',
            'head.marital_status_id.required' => 'Marital status is required.',
            'head.marital_status_id.exists' => 'The selected marital status does not exist.',
            'head.clan_id.exists' => 'The selected clan does not exist.',
            'head.resident_status_id.exists' => 'The selected resident status does not exist.',
            'head.relationship_to_hh_id.exists' => 'The selected relationship does not exist.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateUniqueLotAndBlock($validator);
            $this->validateHouseholdHeadAge($validator);
        });
    }

    private function validateHouseholdHeadAge(Validator $validator): void
    {
        if ($validator->errors()->has('head.date_of_birth')) {
            return;
        }

        $dateOfBirth = $this->input('head.date_of_birth');

        if (! is_string($dateOfBirth) || $dateOfBirth === '') {
            return;
        }

        try {
            $age = Carbon::parse($dateOfBirth)->age;
        } catch (Throwable) {
            return;
        }

        if ($age < 15) {
            $validator->errors()->add(
                'head.date_of_birth',
                'The household head must be at least 15 years old.',
            );
        }
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
