<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Models\HouseholdManagement\Street;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStreetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('street_name'))) {
            return;
        }

        $this->merge([
            'street_name' => Street::standardizeName($this->input('street_name')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'street_name' => [
                'required',
                'string',
                'max:45',
                new ValidPlaceName('Street name'),
                Rule::unique('street', 'street_name'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'street_name.required' => 'Street name is required.',
            'street_name.max' => 'Street name must not exceed 45 characters.',
            'street_name.unique' => 'This street name already exists.',
        ];
    }
}
