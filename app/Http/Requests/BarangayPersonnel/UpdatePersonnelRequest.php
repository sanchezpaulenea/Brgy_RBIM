<?php

namespace App\Http\Requests\BarangayPersonnel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdatePersonnelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'personnel_last_name' => $this->titleCaseName($this->input('personnel_last_name')),
            'personnel_first_name' => $this->titleCaseName($this->input('personnel_first_name')),
            'personnel_middle_name' => $this->titleCaseName($this->input('personnel_middle_name')),
            'personnel_suffix' => $this->titleCaseName($this->input('personnel_suffix')),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'personnel_last_name' => ['required', 'string', 'max:45'],
            'personnel_first_name' => ['required', 'string', 'max:45'],
            'personnel_middle_name' => ['nullable', 'string', 'max:45'],
            'personnel_suffix' => ['nullable', 'string', 'max:10'],
            'personnel_date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'personnel_status_id' => ['required', 'integer', Rule::exists('personnel_status', 'personnel_status_id')],
            'confirm_duplicate' => ['sometimes', 'boolean'],
            'position_id' => ['required', 'integer', Rule::exists('personnel_position', 'position_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'personnel_last_name.required' => 'Last name is required.',
            'personnel_first_name.required' => 'First name is required.',
            'personnel_date_of_birth.required' => 'Date of birth is required.',
            'personnel_date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
            'personnel_status_id.required' => 'Personnel status is required.',
            'personnel_status_id.exists' => 'The selected personnel status does not exist.',
            'position_id.required' => 'Personnel position is required.',
            'position_id.exists' => 'The selected personnel position does not exist.',
        ];
    }

    private function titleCaseName(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $formatted = Str::of($value)->squish()->title()->toString();

        return $formatted === '' ? null : $formatted;
    }
}
