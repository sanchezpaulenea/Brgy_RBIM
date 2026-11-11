<?php

namespace App\Http\Requests\Lookups;

use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMunicipalityCityRequest extends FormRequest
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
            'municipality_city_name' => ['required', 'string', 'max:60', new ValidPlaceName('City / municipality')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'province_id.required' => 'Select a province before adding a city or municipality.',
            'province_id.exists' => 'The selected province does not exist.',
            'municipality_city_name.required' => 'City / municipality is required.',
            'municipality_city_name.max' => 'City / municipality must be 60 characters or fewer.',
        ];
    }
}
