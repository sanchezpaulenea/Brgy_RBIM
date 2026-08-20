<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPositionName implements ValidationRule
{
    public const PATTERN = '/^[\p{L}\p{N}]+(?:[ \-][\p{L}\p{N}]+)*$/u';

    public const MESSAGE = 'Position name must not contain special characters.';

    public const NUMBERS_ONLY_MESSAGE = 'Position name must not contain numbers only.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || preg_match(self::PATTERN, $value) !== 1) {
            $fail(self::MESSAGE);

            return;
        }

        if (preg_match('/\p{L}/u', $value) !== 1) {
            $fail(self::NUMBERS_ONLY_MESSAGE);
        }
    }
}
