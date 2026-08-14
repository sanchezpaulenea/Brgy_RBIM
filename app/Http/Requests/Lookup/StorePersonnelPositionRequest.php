<?php

namespace App\Http\Requests\Lookup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonnelPositionRequest extends FormRequest
{
    /**
     * Authorization is handled in LookupController via LookupPolicy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'position_name' => [
                'required',
                'string',
                'max:45',
                Rule::unique('personnel_position', 'position_name'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'position_name.required' => 'Position name is required.',
            'position_name.unique' => 'This position name already exists.',
        ];
    }
}
