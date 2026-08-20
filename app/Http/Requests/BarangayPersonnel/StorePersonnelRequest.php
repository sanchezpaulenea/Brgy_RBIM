<?php

namespace App\Http\Requests\BarangayPersonnel;

use App\Rules\ValidPositionName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePersonnelRequest extends FormRequest
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
            'position_name' => $this->normalizePositionName($this->input('position_name')),
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
            'personnel_status_id' => ['sometimes', 'integer', Rule::exists('personnel_status', 'personnel_status_id')],
            'confirm_duplicate' => ['sometimes', 'boolean'],
            'position_id' => [
                'required_without:position_name',
                'nullable',
                'integer',
                Rule::exists('personnel_position', 'position_id'),
            ],
            'position_name' => [
                'required_without:position_id',
                'nullable',
                'string',
                'max:45',
                new ValidPositionName,
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
            'personnel_last_name.required' => 'Last name is required.',
            'personnel_first_name.required' => 'First name is required.',
            'personnel_date_of_birth.required' => 'Date of birth is required.',
            'personnel_date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
            'personnel_status_id.exists' => 'The selected personnel status does not exist.',
            'position_id.required_without' => 'Select an existing position or enter a new position name.',
            'position_id.exists' => 'The selected personnel position does not exist.',
            'position_name.required_without' => 'Select an existing position or enter a new position name.',
            'position_name.unique' => 'This position name already exists.',
            'position_name.max' => 'Position name must not exceed 45 characters.',
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

    private function normalizePositionName(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $formatted = Str::of($value)->squish()->toString();

        return $formatted === '' ? null : $formatted;
    }
}
