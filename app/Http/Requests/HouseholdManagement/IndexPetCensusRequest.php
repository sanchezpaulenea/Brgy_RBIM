<?php

namespace App\Http\Requests\HouseholdManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPetCensusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('search') && is_string($this->input('search'))) {
            $search = trim($this->input('search'));
            $this->merge(['search' => $search === '' ? null : $search]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'pet_status_id' => ['sometimes', 'integer', Rule::exists('pet_status', 'pet_status_id')],
            'specie_id' => ['sometimes', 'integer', Rule::exists('specie', 'specie_id')],
            'breed_id' => ['sometimes', 'integer', Rule::exists('breed', 'breed_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pet_status_id.exists' => 'The selected pet status does not exist.',
            'specie_id.exists' => 'The selected species does not exist.',
            'breed_id.exists' => 'The selected breed does not exist.',
        ];
    }

    /**
     * @return array{search?: string|null, pet_status_id?: int, specie_id?: int, breed_id?: int}
     */
    public function filters(): array
    {
        return array_filter(
            $this->validated(),
            fn (mixed $value) => $value !== null && $value !== '',
        );
    }
}
