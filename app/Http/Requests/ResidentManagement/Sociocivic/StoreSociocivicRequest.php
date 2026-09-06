<?php

namespace App\Http\Requests\ResidentManagement\Sociocivic;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSociocivicRequest extends FormRequest
{
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased(['registered_barangay_voter']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'solo_parent_status_id' => [
                'required',
                'integer',
                Rule::exists('solo_parent_status', 'solo_parent_status_id'),
            ],
            'registered_sen_citizen' => ['required', 'boolean'],
            'registered_barangay_voter' => ['nullable', 'string', 'max:45'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'solo_parent_status_id.exists' => 'The selected solo parent status does not exist.',
        ];
    }
}
