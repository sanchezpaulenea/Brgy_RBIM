<?php

namespace App\Http\Requests\ResidentManagement\Demographics;

use App\Models\ResidentManagement\Demographic\Nationality;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNationalityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('nationality'))) {
            return;
        }

        $this->merge([
            'nationality' => Nationality::standardizeName($this->input('nationality')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nationality' => [
                'required',
                'string',
                'max:45',
                new ValidPlaceName('Nationality'),
                Rule::unique('nationality', 'nationality'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nationality.required' => 'Nationality is required.',
            'nationality.max' => 'Nationality must not exceed 45 characters.',
            'nationality.unique' => 'This nationality already exists.',
        ];
    }
}
