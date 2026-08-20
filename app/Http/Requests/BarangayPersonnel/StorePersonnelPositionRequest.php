<?php

namespace App\Http\Requests\BarangayPersonnel;

use App\Rules\ValidPositionName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePersonnelPositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! is_string($this->input('position_name'))) {
            return;
        }

        $this->merge([
            'position_name' => Str::of($this->input('position_name'))->squish()->toString(),
        ]);
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
            'position_name.required' => 'Position name is required.',
            'position_name.max' => 'Position name must not exceed 45 characters.',
            'position_name.unique' => 'This position name already exists.',
        ];
    }
}
