<?php

namespace App\Http\Requests\Lookups;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListMunicipalitiesRequest extends FormRequest
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
        return [
            'province_id' => ['required', 'integer', Rule::exists('province', 'province_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'province_id.required' => 'Select a province first.',
            'province_id.exists' => 'The selected province does not exist.',
        ];
    }
}
