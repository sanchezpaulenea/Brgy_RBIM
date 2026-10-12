<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;

class SearchCertificateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('search')) {
            $this->merge([
                'search' => trim((string) $this->input('search')),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'search.required' => 'Enter a resident name to search.',
            'search.min' => 'Enter at least 2 characters to search.',
            'search.max' => 'Resident search must not exceed 100 characters.',
        ];
    }
}
