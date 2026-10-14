<?php

namespace App\Http\Requests\LogRetention;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLogRetentionRequest extends FormRequest
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
            'audit_log_retention_days' => ['bail', 'required', 'integer', 'min:30', 'max:3650'],
            'user_log_retention_days' => ['bail', 'required', 'integer', 'min:30', 'max:3650'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'audit_log_retention_days.required' => 'Audit log retention period (days) is required.',
            'audit_log_retention_days.integer' => 'Audit log retention period (days) must be a whole number.',
            'audit_log_retention_days.min' => 'Audit log retention period (days) must be at least 30.',
            'audit_log_retention_days.max' => 'Audit log retention period (days) must not be greater than 3650.',
            'user_log_retention_days.required' => 'User log retention period (days) is required.',
            'user_log_retention_days.integer' => 'User log retention period (days) must be a whole number.',
            'user_log_retention_days.min' => 'User log retention period (days) must be at least 30.',
            'user_log_retention_days.max' => 'User log retention period (days) must not be greater than 3650.',
        ];
    }
}
