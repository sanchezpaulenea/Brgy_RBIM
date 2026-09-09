<?php

namespace App\Http\Requests\ResidentManagement\Ctc;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCtcRequest extends FormRequest
{
    use RequiresAtLeastOneField;

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
            'has_valid_ctc' => ['sometimes', 'required', 'boolean'],
            'ctc_issued_here' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne(
                    $validator,
                    ['has_valid_ctc', 'ctc_issued_here'],
                    'ctc',
                    'Provide at least one community tax certificate field to update.',
                );
            },
        ];
    }
}
