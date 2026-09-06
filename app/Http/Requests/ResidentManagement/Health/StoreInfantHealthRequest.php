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
        $this->mergeTitleCased(['immunization']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'place_of_delivery_id' => ['required', 'integer', Rule::exists('place_of_delivery', 'place_of_delivery_id')],
            'birth_attendant_id' => ['required', 'integer', Rule::exists('birth_attendant', 'birth_attendant_id')],
            'immunization' => ['required', 'string', 'max:45'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'place_of_delivery_id.exists' => 'The selected place of delivery does not exist.',
            'birth_attendant_id.exists' => 'The selected birth attendant does not exist.',
            'immunization.required' => 'Immunization is required.',
        ];
    }
}
