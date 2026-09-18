<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPwdIdNumber implements ValidationRule
{
    public const DIGITS = 16;

    public const PATTERN = '/^\d{16}$/';

    public const MESSAGE = 'PWD ID number must be exactly 16 digits.';

    /**
     * Leading zeros are significant, so the value is kept as a digit string.
     */
    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $value) ?? '';

        return $digits === '' ? null : $digits;
    }

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
