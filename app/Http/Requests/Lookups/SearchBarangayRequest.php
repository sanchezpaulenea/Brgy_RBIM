<?php

namespace App\Http\Requests\Lookups;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchBarangayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $term = $this->input('q');

        if (is_string($term)) {
            $trimmed = preg_replace('/\s+/u', ' ', trim($term)) ?? '';
            $this->merge([
                'q' => $trimmed === '' ? null : $trimmed,
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'q' => ['required_without:municipality_city_id', 'nullable', 'string', 'min:2', 'max:60', 'regex:/\p{L}/u'],
            'municipality_city_id' => ['nullable', 'integer', Rule::exists('municipality_city', 'municipality_city_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.required' => 'Enter a barangay name to search.',
            'q.required_without' => 'Enter a barangay name to search.',
            'q.min' => 'Enter at least two characters of the barangay name.',
            'q.max' => 'Barangay search must be 60 characters or fewer.',
            'q.regex' => 'Barangay search must include a letter.',
        ];
    }
}
