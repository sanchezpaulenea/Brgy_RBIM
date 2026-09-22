<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInfantHealthRequest extends FormRequest
{
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
                'required_without:place_of_delivery',
                'nullable',
                'integer',
                Rule::exists('place_of_delivery', 'place_of_delivery_id'),
            ],
            'place_of_delivery' => ['required_without:place_of_delivery_id', 'nullable', 'string', 'max:45'],
            'birth_attendant_id' => [
                'required_without:birth_attendant',
                'nullable',
                'integer',
                Rule::exists('birth_attendant', 'birth_attendant_id'),
            ],
            'birth_attendant' => ['required_without:birth_attendant_id', 'nullable', 'string', 'max:45'],
            'immunization_id' => [
                'required_without:immunization',
                'nullable',
                'integer',
                Rule::exists('immunization', 'immunization_id'),
            ],
            'immunization' => ['required_without:immunization_id', 'nullable', 'string', 'max:45'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'place_of_delivery_id.required_without' => 'Place of delivery is required.',
            'place_of_delivery.required_without' => 'Place of delivery is required.',
            'birth_attendant_id.required_without' => 'Birth attendant is required.',
            'birth_attendant.required_without' => 'Birth attendant is required.',
            'immunization_id.required_without' => 'Immunization is required.',
            'immunization.required_without' => 'Immunization is required.',
        ];
    }
}
