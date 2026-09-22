<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateInfantHealthRequest extends FormRequest
{
    use RequiresAtLeastOneField;
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased(['place_of_delivery', 'birth_attendant', 'immunization']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'place_of_delivery_id' => [
                'sometimes',
                'required_without:place_of_delivery',
                'nullable',
                'integer',
                Rule::exists('place_of_delivery', 'place_of_delivery_id'),
            ],
            'place_of_delivery' => ['sometimes', 'required_without:place_of_delivery_id', 'nullable', 'string', 'max:45'],
            'birth_attendant_id' => [
                'sometimes',
                'required_without:birth_attendant',
                'nullable',
                'integer',
                Rule::exists('birth_attendant', 'birth_attendant_id'),
            ],
            'birth_attendant' => ['sometimes', 'required_without:birth_attendant_id', 'nullable', 'string', 'max:45'],
            'immunization_id' => [
                'sometimes',
                'required_without:immunization',
                'nullable',
                'integer',
                Rule::exists('immunization', 'immunization_id'),
            ],
            'immunization' => ['sometimes', 'required_without:immunization_id', 'nullable', 'string', 'max:45'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'place_of_delivery_id',
                    'place_of_delivery',
                    'birth_attendant_id',
                    'birth_attendant',
                    'immunization_id',
                    'immunization',
                ], 'infant_health', 'Provide at least one infant health field to update.');
            },
        ];
    }
}
