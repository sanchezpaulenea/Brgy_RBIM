<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNcscRrn implements ValidationRule
{
    public const PATTERN = '/^\d{6}$/';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            $fail('NCSC-RRN must be a 6-digit Registration Reference Number.');

            return;
        }

        if (preg_match(self::PATTERN, trim((string) $value)) !== 1) {
            $fail('NCSC-RRN must be a 6-digit Registration Reference Number.');
        }
    }
}
