<?php

namespace App\Http\Requests\ResidentManagement\Demographics;

use App\Models\ResidentManagement\Demographic\Ethnicity;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEthnicityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('ethnicity'))) {
            return;
        }

        $this->merge([
            'ethnicity' => Ethnicity::standardizeName($this->input('ethnicity')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'ethnicity' => [
                'required',
                'string',
                'max:45',
                new ValidPlaceName('Ethnicity'),
                Rule::unique('ethnicity', 'ethnicity'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ethnicity.required' => 'Ethnicity is required.',
            'ethnicity.max' => 'Ethnicity must not exceed 45 characters.',
            'ethnicity.unique' => 'This ethnicity already exists.',
        ];
    }
}
