<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidDefaultPassword implements ValidationRule
{
    public const MIN_LENGTH = 8;

    public const NO_SPACES_MESSAGE = 'Default password must not contain spaces.';

    public const LENGTH_MESSAGE = 'Default password must be at least 8 characters long.';

    public const COMPLEXITY_MESSAGE = 'Default password must include at least one uppercase letter, one lowercase letter, and one number.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(self::LENGTH_MESSAGE);

            return;
        }

        if (preg_match('/\s/u', $value) === 1) {
            $fail(self::NO_SPACES_MESSAGE);

            return;
        }

        if (mb_strlen($value) < self::MIN_LENGTH) {
            $fail(self::LENGTH_MESSAGE);

            return;
        }

        if (preg_match('/[A-Z]/', $value) !== 1
            || preg_match('/[a-z]/', $value) !== 1
            || preg_match('/\d/', $value) !== 1) {
            $fail(self::COMPLEXITY_MESSAGE);
        }
    }
}
