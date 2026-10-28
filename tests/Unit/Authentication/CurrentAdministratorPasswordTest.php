<?php

namespace Tests\Unit\Authentication;

use App\Models\UserManagement\User;
use App\Rules\CurrentAdministratorPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class CurrentAdministratorPasswordTest extends TestCase
{
    protected function tearDown(): void
    {
        RateLimiter::clear('administrator-password:42');
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_accepts_the_signed_in_administrator_password(): void
    {
        $validator = $this->validatorFor($this->administrator('AdminPass1'), 'AdminPass1');

        $this->assertFalse($validator->fails(), (string) $validator->errors());
    }

    public function test_it_rejects_a_wrong_password(): void
    {
        $validator = $this->validatorFor($this->administrator('AdminPass1'), 'WrongPass1');

        $this->assertTrue($validator->fails());
        $this->assertSame(CurrentAdministratorPassword::INCORRECT, $validator->errors()->first('admin_password'));
    }

    public function test_it_rejects_a_non_administrator_password(): void
    {
        $user = $this->administrator('AdminPass1', false);
        $validator = $this->validatorFor($user, 'AdminPass1');

        $this->assertTrue($validator->fails());
        $this->assertSame(CurrentAdministratorPassword::INCORRECT, $validator->errors()->first('admin_password'));
    }

    public function test_it_locks_confirmation_after_repeated_wrong_passwords(): void
    {
        $user = $this->administrator('AdminPass1');

        for ($attempt = 0; $attempt < CurrentAdministratorPassword::MAX_ATTEMPTS; $attempt++) {
            $failed = $this->validatorFor($user, 'WrongPass1');
            $this->assertTrue($failed->fails(), (string) $failed->errors());
        }

        $validator = $this->validatorFor($user, 'AdminPass1');

        $this->assertTrue($validator->fails());
        $this->assertSame(CurrentAdministratorPassword::TOO_MANY, $validator->errors()->first('admin_password'));
    }

    private function validatorFor(User $user, string $password): \Illuminate\Validation\Validator
    {
        return Validator::make(
            ['admin_password' => $password],
            ['admin_password' => [new CurrentAdministratorPassword($user)]],
        );
    }

    private function administrator(string $password, bool $isAdministrator = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->user_id = 42;
        $user->password_hash = Hash::make($password);
        $user->shouldReceive('isSystemAdministrator')->andReturn($isAdministrator);

        return $user;
    }
}
