<?php

namespace App\Http\Requests\SystemSetting;

use App\Models\Setting\Setting;
use App\Rules\ValidBarangayAddress;
use App\Rules\ValidBarangayContactNumber;
use App\Rules\ValidDefaultPassword;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('setting_value')) {
            return;
        }

        $value = $this->input('setting_value');

        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

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
        return [
            'setting_value' => $this->rulesForSetting($this->resolveSetting()),
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
            'setting_value.between' => 'Setting value must be between :min and :max.',
            'setting_value.string' => 'Setting value must be text.',
            'setting_value.max' => 'Setting value must not exceed 45 characters.',
            'setting_value.boolean' => 'Setting value must be true or false.',
            'setting_value.regex' => 'Barangay code must be exactly 10 digits, following the Philippine Standard Geographic Code (PSGC) format.',
            'setting_value.email' => 'Please enter a valid email address.',
        ];
    }

    /**
     * Normalize validated values to the varchar column format.
     */
    public function normalizedValue(): string
    {
        $setting = $this->resolveSetting();
        $value = $this->validated('setting_value');

        return match ($setting->data_type) {
            'int' => (string) $value,
            'bool' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    /**
     * FormRequest::rules() can run while `{setting}` is still the raw ID string.
     * Load the row ourselves so key-specific rules always apply.
     */
    private function resolveSetting(): Setting
    {
        $parameter = $this->route('setting');

        if ($parameter instanceof Setting) {
            return $parameter;
        }

        return Setting::query()
            ->where('setting_id', $parameter)
            ->firstOrFail();
    }

    /**
     * @return array<int, mixed>
     */
    private function rulesForSetting(Setting $setting): array
    {
        return match ($setting->setting_key) {
            'barangay_name' => ['required', 'string', 'max:45', new ValidPlaceName('Barangay name')],
            'city_name' => ['required', 'string', 'max:45', new ValidPlaceName('City name')],
            'barangay_address' => ['required', 'string', 'max:45', new ValidBarangayAddress],
            'barangay_code' => ['required', 'string', 'regex:/^\d{10}$/'],
            'barangay_contact_no' => ['required', 'string', 'max:45', new ValidBarangayContactNumber],
            'barangay_email' => ['required', 'string', 'max:45', 'email'],
            'default_password' => ['required', 'string', 'max:45', new ValidDefaultPassword],
            'password_min_length' => ['required', 'integer', 'between:8,32'],
            'max_login_attempts' => ['required', 'integer', 'between:3,10'],
            'account_lockout_minutes' => ['required', 'integer', 'between:1,1440'],
            'session_timeout_minutes' => ['required', 'integer', 'between:5,480'],
            'audit_log_retention_days' => ['required', 'integer', 'between:30,3650'],
            default => match ($setting->data_type) {
                'int' => ['required', 'integer', 'min:1'],
                'bool' => ['required', 'boolean'],
                default => ['required', 'string', 'max:45'],
            },
        };
    }
}
