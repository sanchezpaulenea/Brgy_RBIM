<?php

namespace App\Rules;

use App\Models\UserManagement\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class CurrentAdministratorPassword implements ValidationRule
{
    public const INCORRECT = 'The password is incorrect.';

    public const TOO_MANY = 'Too many incorrect passwords. Please wait a minute and try again.';

    public const MAX_ATTEMPTS = 5;

    public const DECAY_SECONDS = 60;

    public function __construct(private ?User $user = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = $this->resolveUser();

        if (! $user instanceof User) {
            $fail(self::INCORRECT);

            return;
        }

        $key = 'administrator-password:'.$user->getKey();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $fail(self::TOO_MANY);

            return;
        }

        $password = is_string($value) ? $value : '';
        $hash = $user->password_hash;
        $matches = $password !== ''
            && is_string($hash)
            && $hash !== ''
            && Hash::check($password, $hash)
            && $user->isSystemAdministrator();

        if (! $matches) {
            RateLimiter::hit($key, self::DECAY_SECONDS);
            $fail(self::INCORRECT);

            return;
        }

        RateLimiter::clear($key);
    }

    private function resolveUser(): ?User
    {
        if ($this->user instanceof User) {
            return $this->user;
        }

        $authenticated = Auth::user();

        return $authenticated instanceof User ? $authenticated : null;
    }
}
