<?php

namespace App\Http\Requests\Logs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUserLogRequest extends FormRequest
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
            'source' => ['sometimes', 'string', Rule::in(['active', 'archive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'source.in' => 'Source must be active or archive.',
        ];
    }

    public function source(): string
    {
        $source = $this->validated('source');

        return is_string($source) && $source !== '' ? $source : 'active';
    }
}
