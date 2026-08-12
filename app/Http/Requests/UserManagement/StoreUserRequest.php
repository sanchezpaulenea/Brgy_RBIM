<?php

namespace App\Http\Requests\UserManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:45', Rule::unique('user', 'username')],
            'personnel_id' => [
                'required_without:position_id',
                'nullable',
                'integer',
                Rule::exists('barangay_personnel', 'personnel_id'),
                Rule::unique('user', 'personnel_id'),
            ],
            'position_id' => [
                'required_without:personnel_id',
                'nullable',
                'integer',
                Rule::exists('personnel_position', 'position_id'),
            ],
            'role_id' => ['required', 'integer', Rule::exists('role', 'role_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username is required.',
            'username.unique' => 'This username is already taken.',
            'personnel_id.required_without' => 'Select an available personnel position or choose a position to assign.',
            'personnel_id.exists' => 'The selected personnel record does not exist.',
            'personnel_id.unique' => 'This personnel record is already linked to another user account.',
            'position_id.required_without' => 'Select an available personnel position or choose a position to assign.',
            'position_id.exists' => 'The selected personnel position does not exist.',
            'role_id.required' => 'Role is required.',
            'role_id.exists' => 'The selected role does not exist.',
        ];
    }
}
