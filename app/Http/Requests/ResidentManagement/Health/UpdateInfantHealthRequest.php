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
        $this->mergeTitleCased(['immunization']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'place_of_delivery_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('place_of_delivery', 'place_of_delivery_id'),
            ],
            'birth_attendant_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('birth_attendant', 'birth_attendant_id'),
            ],
            'immunization' => ['sometimes', 'required', 'string', 'max:45'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'place_of_delivery_id',
                    'birth_attendant_id',
                    'immunization',
                ], 'infant_health', 'Provide at least one infant health field to update.');
            },
        ];
    }
}
