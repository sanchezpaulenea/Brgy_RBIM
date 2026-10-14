<?php

namespace App\Http\Requests\Logs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAuditLogRequest extends FormRequest
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
            'user_id' => ['sometimes', 'integer', 'min:1'],
            'username' => ['sometimes', 'string', 'max:45'],
            'entity' => ['sometimes', 'string', 'max:45'],
            'target' => ['sometimes', 'string', 'max:45'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
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

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $filters = $this->validated();
        $filters['source'] = $filters['source'] ?? 'active';

        return $filters;
    }
}
