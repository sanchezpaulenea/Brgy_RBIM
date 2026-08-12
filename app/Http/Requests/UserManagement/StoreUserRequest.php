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
                'required',
                'integer',
                Rule::exists('barangay_personnel', 'personnel_id'),
                Rule::unique('user', 'personnel_id'),
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
            'personnel_id.required' => 'Personnel is required.',
            'personnel_id.exists' => 'The selected personnel record does not exist.',
            'personnel_id.unique' => 'This personnel record is already linked to another user account.',
            'role_id.required' => 'Role is required.',
            'role_id.exists' => 'The selected role does not exist.',
        ];
    }
}
