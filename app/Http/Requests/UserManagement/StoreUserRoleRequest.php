<?php

namespace App\Http\Requests\UserManagement;

use App\Models\UserManagement\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRoleRequest extends FormRequest
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
        /** @var User $user */
        $user = $this->route('user');

        return [
            'role_id' => [
                'required',
                'integer',
                Rule::exists('role', 'role_id'),
                Rule::unique('user_role', 'role_id')->where(
                    fn ($query) => $query->where('user_id', $user->user_id)
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role_id.required' => 'Role is required.',
            'role_id.exists' => 'The selected role does not exist.',
            'role_id.unique' => 'This role is already assigned to the user.',
        ];
    }
}
