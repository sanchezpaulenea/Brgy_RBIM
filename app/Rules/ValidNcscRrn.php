<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNcscRrn implements ValidationRule
{
    public const MIN_DIGITS = 4;

    public const MAX_DIGITS = 12;

    public const PATTERN = '/^\d{4,12}$/';

    public const MESSAGE = 'NCSC-RRN must be a Registration Reference Number of 4 to 12 digits.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            $fail(self::MESSAGE);

            return;
        }

        if (preg_match(self::PATTERN, trim((string) $value)) !== 1) {
            $fail(self::MESSAGE);
        }
    }
}
