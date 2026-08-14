<?php

namespace App\Http\Requests\Lookup;

use App\Policies\Lookup\LookupPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLookupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return match ($this->route('type')) {
            LookupPolicy::PERSONNEL_POSITION => [
                'position_name' => [
                    'required',
                    'string',
                    'max:45',
                    Rule::unique('personnel_position', 'position_name'),
                ],
            ],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'position_name.required' => 'Position name is required.',
            'position_name.max' => 'Position name must not exceed 45 characters.',
            'position_name.unique' => 'This position name already exists.',
        ];
    }
}
