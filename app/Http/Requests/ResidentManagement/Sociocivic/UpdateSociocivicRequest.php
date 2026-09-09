<?php

namespace App\Http\Requests\ResidentManagement\Sociocivic;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Rules\ValidNcscRrn;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSociocivicRequest extends FormRequest
{
    use RequiresAtLeastOneField;
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased(['registered_barangay_voter']);
        $this->nullBlankStrings([
            'registered_barangay_voter',
            'ncsc_rrn_id_number',
            'osca_id_number',
        ]);

        if ($this->exists('ncsc_rrn_id_number') && is_string($this->input('ncsc_rrn_id_number'))) {
            $this->merge(['ncsc_rrn_id_number' => trim($this->input('ncsc_rrn_id_number'))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'solo_parent_status_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('solo_parent_status', 'solo_parent_status_id'),
            ],
            'registered_sen_citizen' => ['sometimes', 'required', 'boolean'],
            'ncsc_rrn_id_number' => ['sometimes', 'nullable', 'string', 'max:45', new ValidNcscRrn],
            'osca_id_number' => ['sometimes', 'nullable', 'string', 'max:45'],
            'registered_barangay_voter' => [
                'sometimes',
                'nullable',
                'string',
                'max:45',
                Rule::notIn(['Yes', 'No']),
                new ValidPlaceName('Registered voter barangay'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'solo_parent_status_id.exists' => 'The selected solo parent status does not exist.',
            'registered_barangay_voter.not_in' => 'Enter the barangay where the resident is a registered voter.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'solo_parent_status_id',
                    'registered_sen_citizen',
                    'ncsc_rrn_id_number',
                    'osca_id_number',
                    'registered_barangay_voter',
                ], 'sociocivic', 'Provide at least one sociocivic field to update.');
            },
        ];
    }

    /**
     * @param  list<string>  $fields
     */
    private function nullBlankStrings(array $fields): void
    {
        $merge = [];

        foreach ($fields as $field) {
            if ($this->exists($field) && $this->input($field) === '') {
                $merge[$field] = null;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
