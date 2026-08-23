<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBarangayAddress implements ValidationRule
{
    public const MIN_LENGTH = 10;

    public const MESSAGE = 'Please enter a valid barangay address.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(self::MESSAGE);

            return;
        }

        $address = trim($value);

        if (mb_strlen($address) < self::MIN_LENGTH) {
            $fail(self::MESSAGE);

            return;
        }

        if (preg_match('/\p{L}/u', $address) !== 1) {
            $fail(self::MESSAGE);

            return;
        }

        $compact = preg_replace('/\s+/u', '', $address) ?? '';

        if ($compact !== '' && preg_match('/^(.)\1+$/u', $compact) === 1) {
            $fail(self::MESSAGE);
        }
    }
}
