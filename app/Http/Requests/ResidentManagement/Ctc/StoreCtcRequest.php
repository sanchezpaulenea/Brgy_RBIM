<?php

namespace App\Http\Requests\ResidentManagement\Ctc;

use Illuminate\Foundation\Http\FormRequest;

class StoreCtcRequest extends FormRequest
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
            'has_valid_ctc' => ['required', 'boolean'],
            'ctc_issued_here' => ['nullable', 'boolean'],
        ];
    }
}
