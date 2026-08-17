<?php

namespace App\Http\Requests\UserManagement;

use App\Models\UserManagement\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
                'nullable',
                'integer',
                Rule::exists('barangay_personnel', 'personnel_id'),
                Rule::unique('user', 'personnel_id'),
            ],
            'position_id' => [
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
            'personnel_id.exists' => 'The selected personnel record does not exist.',
            'personnel_id.unique' => 'This personnel record is already linked to another user account.',
            'position_id.exists' => 'The selected personnel position does not exist.',
            'role_id.required' => 'Role is required.',
            'role_id.exists' => 'The selected role does not exist.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $role = Role::query()->where('role_id', $this->integer('role_id'))->first();
                $isGuest = $role?->role_name === Role::GUEST;
                $hasPersonnel = $this->filled('personnel_id') || $this->filled('position_id');

                if ($isGuest && $hasPersonnel) {
                    $validator->errors()->add(
                        'personnel_id',
                        'Guest is not offered for personnel-linked accounts.'
                    );
                }

                if (! $isGuest && ! $hasPersonnel) {
                    $validator->errors()->add(
                        'personnel_id',
                        'Staff roles require a linked personnel record.'
                    );
                }
            },
        ];
    }
}
