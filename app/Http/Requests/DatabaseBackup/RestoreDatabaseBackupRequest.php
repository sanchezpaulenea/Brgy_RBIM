<?php

namespace App\Http\Requests\DatabaseBackup;

use Illuminate\Foundation\Http\FormRequest;

class RestoreDatabaseBackupRequest extends FormRequest
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
        $maxKilobytes = (int) config('database_backup.max_upload_kilobytes', 32768);

        return [
            'backup' => ['bail', 'required', 'file', 'extensions:sql', 'max:'.$maxKilobytes],
            'confirmation' => ['bail', 'required', 'string', 'in:RESTORE'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'backup.required' => 'Choose a SQL backup file.',
            'backup.file' => 'Choose a SQL backup file.',
            'backup.extensions' => 'The backup must be a .sql file.',
            'backup.max' => 'The backup file must not be larger than 32 MB.',
            'confirmation.required' => 'Type RESTORE to confirm.',
            'confirmation.in' => 'Type RESTORE to confirm.',
        ];
    }
}
