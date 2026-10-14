<?php

namespace App\Http\Requests\LogRetention;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArchiveLogRetentionRequest extends FormRequest
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
            'log' => ['bail', 'required', 'string', Rule::in(['audit', 'user'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'log.required' => 'Choose Audit Log or User Log.',
            'log.in' => 'Log must be audit or user.',
        ];
    }
}
