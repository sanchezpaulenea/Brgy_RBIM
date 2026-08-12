<?php

namespace App\Http\Requests\Setting;

use App\Models\Setting\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
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
        /** @var Setting $setting */
        $setting = $this->route('setting');

        return [
            'setting_value' => match ($setting->data_type) {
                'int' => ['required', 'integer', 'min:1'],
                'bool' => ['required', 'boolean'],
                default => ['required', 'string', 'max:45'],
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'setting_value.required' => 'Setting value is required.',
            'setting_value.integer' => 'Setting value must be a whole number.',
            'setting_value.min' => 'Setting value must be at least 1.',
            'setting_value.string' => 'Setting value must be text.',
            'setting_value.max' => 'Setting value must not exceed 45 characters.',
            'setting_value.boolean' => 'Setting value must be true or false.',
        ];
    }

    /**
     * Normalize validated values to the varchar column format.
     */
    public function normalizedValue(): string
    {
        /** @var Setting $setting */
        $setting = $this->route('setting');
        $value = $this->validated('setting_value');

        return match ($setting->data_type) {
            'int' => (string) $value,
            'bool' => $value ? '1' : '0',
            default => (string) $value,
        };
    }
}
