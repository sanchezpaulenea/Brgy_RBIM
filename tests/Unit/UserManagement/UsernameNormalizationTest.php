<?php

namespace Tests\Unit\UserManagement;

use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Requests\UserManagement\StoreUserRequest;
use App\Models\UserManagement\User;
use ReflectionMethod;
use Tests\TestCase;

class UsernameNormalizationTest extends TestCase
{
    public function test_user_model_stores_username_in_lowercase(): void
    {
        $user = new User(['username' => 'AdminUser']);

        $this->assertSame('adminuser', $user->username);
    }

    public function test_display_username_capitalizes_without_forcing_lowercase(): void
    {
        $this->assertSame('Neilpadiernos', User::formatForDisplay('neilpadiernos'));
        $this->assertSame('Neil Padiernos', User::formatForDisplay('neil padiernos'));
    }

    public function test_store_request_lowercases_username(): void
    {
        $request = StoreUserRequest::create('/api/v1/users', 'POST', [
            'username' => ' AdminUser ',
            'role_id' => 1,
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertSame('adminuser', $request->input('username'));
    }

    public function test_login_request_lowercases_username(): void
    {
        $request = LoginRequest::create('/api/v1/auth/login', 'POST', [
            'username' => 'AdminUser',
            'password' => 'secret',
        ]);

        $this->invokePrepareForValidation($request);

        $this->assertSame('adminuser', $request->input('username'));
    }

    private function invokePrepareForValidation(StoreUserRequest|LoginRequest $request): void
    {
        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->invoke($request);
    }
}
