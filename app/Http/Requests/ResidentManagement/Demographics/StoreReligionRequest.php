<?php

namespace App\Http\Requests\ResidentManagement\Demographics;

use App\Models\ResidentManagement\Demographic\Religion;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReligionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('religion'))) {
            return;
        }

        $this->merge([
            'religion' => Religion::standardizeName($this->input('religion')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'religion' => [
                'required',
                'string',
                'max:45',
                new ValidPlaceName('Religion'),
                Rule::unique('religion', 'religion'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'religion.required' => 'Religion is required.',
            'religion.max' => 'Religion must not exceed 45 characters.',
            'religion.unique' => 'This religion already exists.',
        ];
    }
}
