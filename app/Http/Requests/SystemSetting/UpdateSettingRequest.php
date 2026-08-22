<?php

namespace App\Http\Requests\SystemSetting;

use App\Models\Setting\Setting;
use App\Rules\ValidBarangayAddress;
use App\Rules\ValidBarangayContactNumber;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $value = $this->input('setting_value');

        if (is_string($value)) {
            $this->merge([
                'setting_value' => trim($value),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Setting $setting */
        $setting = $this->route('setting');

        return [
            'setting_value' => $this->rulesForSetting($setting),
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
            'setting_value.regex' => 'Barangay code must be a valid 10-digit PSGC code.',
            'setting_value.email' => 'Please enter a valid email address.',
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

    /**
     * @return array<int, mixed>
     */
    private function rulesForSetting(Setting $setting): array
    {
        return match ($setting->setting_key) {
            'barangay_address' => ['required', 'string', 'max:45', new ValidBarangayAddress],
            'barangay_code' => [
                'required',
                'string',
                // PSGC is a 10-digit numeric code (e.g. seed value 1430300006).
                // Future: cross-check against a local PSA PSGC reference list
                // shipped with the app — no external API call required.
                'regex:/^\d{10}$/',
            ],
            'barangay_contact_no' => ['required', 'string', 'max:45', new ValidBarangayContactNumber],
            'barangay_email' => [
                'required',
                'string',
                'max:45',
                // RFC only: `email:rfc,dns` is not used because this environment
                // may run offline and MX lookups can reject valid local addresses.
                'email:rfc',
            ],
            default => match ($setting->data_type) {
                'int' => ['required', 'integer', 'min:1'],
                'bool' => ['required', 'boolean'],
                default => ['required', 'string', 'max:45'],
            },
        };
    }
}
