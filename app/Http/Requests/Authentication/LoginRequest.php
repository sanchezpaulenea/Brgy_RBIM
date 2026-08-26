<?php

namespace App\Http\Requests\Authentication;

use App\Models\UserManagement\User;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Anyone can attempt to log in — no prior auth check needed.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $username = $this->input('username');

        if (is_string($username)) {
            $this->merge([
                'username' => User::standardizeUsername($username),
            ]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:45'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Please enter your username.',
            'password.required' => 'Please enter your password.',
        ];
    }

    /**
     * A blank username and password share one message instead of two field errors.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $username = $this->input('username');
            $password = $this->input('password');
            $usernameEmpty = ! is_string($username) || trim($username) === '';
            $passwordEmpty = ! is_string($password) || $password === '';

            if ($usernameEmpty && $passwordEmpty) {
                $validator->errors()->forget('username');
                $validator->errors()->forget('password');
                $validator->errors()->add('credentials', 'Please enter your username and password.');
            }
        });
    }
}
