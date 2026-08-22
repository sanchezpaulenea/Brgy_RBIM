<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPositionName implements ValidationRule
{
    public const PATTERN = '/^\p{L}+(?: \p{L}+)*$/u';

    public const MESSAGE = 'Position name may only contain letters and spaces.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || preg_match(self::PATTERN, $value) !== 1) {
            $fail(self::MESSAGE);
        }
    }
}
