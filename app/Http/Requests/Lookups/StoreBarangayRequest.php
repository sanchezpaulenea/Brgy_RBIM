<?php

namespace App\Http\Requests\Lookups;

use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBarangayRequest extends FormRequest
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
            'municipality_city_id' => ['required', 'integer', Rule::exists('municipality_city', 'municipality_city_id')],
            'barangay_name' => ['required', 'string', 'max:60', new ValidPlaceName('Barangay')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'municipality_city_id.required' => 'Select a city or municipality before adding a barangay.',
            'municipality_city_id.exists' => 'The selected city or municipality does not exist.',
            'barangay_name.required' => 'Barangay is required.',
            'barangay_name.max' => 'Barangay must be 60 characters or fewer.',
        ];
    }
}
