<?php

namespace App\Http\Requests\DatabaseBackup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportDatabaseBackupRequest extends FormRequest
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
            'format' => ['bail', 'required', 'string', Rule::in(['sql', 'excel', 'pdf'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'format.required' => 'Choose SQL, Excel, or PDF.',
            'format.in' => 'Format must be SQL, Excel, or PDF.',
        ];
    }
}
