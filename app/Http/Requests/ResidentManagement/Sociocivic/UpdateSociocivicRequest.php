<?php

namespace App\Http\Requests\ResidentManagement\Sociocivic;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Http\Requests\Concerns\TitleCasesAttributes;
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
            'registered_barangay_voter' => ['sometimes', 'nullable', 'string', 'max:45'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'solo_parent_status_id',
                    'registered_sen_citizen',
                    'registered_barangay_voter',
                ], 'sociocivic', 'Provide at least one sociocivic field to update.');
            },
        ];
    }
}
