<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Solo Parent ID: a 10-digit PSGC code, the year and month of issuance, and a
 * 6-digit registry count, stored as PPPPPPPPPP-YYYY-MM-NNNNNN.
 */
class ValidSoloParentIdNumber implements ValidationRule
{
    public const CANONICAL_PATTERN = '/^(\d{10})-(\d{4})-(\d{2})-(\d{6})$/';

    public const TOTAL_DIGITS = 22;

    public const EARLIEST_YEAR = 2000;

    public const MESSAGE = 'Solo Parent ID must be a 10-digit PSGC code, the year and month of issuance, and a 6-digit registry count (e.g. 1234567890-2026-03-000042).';

    /**
     * Accepts the canonical form or 22 bare digits and returns the canonical form.
     */
    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $value) ?? '';

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) !== self::TOTAL_DIGITS) {
            // Leave malformed input untouched so validation can report it.
            return trim((string) $value);
        }

        return sprintf(
            '%s-%s-%s-%s',
            substr($digits, 0, 10),
            substr($digits, 10, 4),
            substr($digits, 14, 2),
            substr($digits, 16, 6),
        );
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

        if (preg_match(self::CANONICAL_PATTERN, trim((string) $value), $matches) !== 1) {
            $fail(self::MESSAGE);

            return;
        }

        [, , $year, $month] = $matches;

        if ((int) $month < 1 || (int) $month > 12) {
            $fail('Solo Parent ID month of issuance must be between 01 and 12.');

            return;
        }

        if ((int) $year < self::EARLIEST_YEAR || (int) $year > (int) date('Y')) {
            $fail(sprintf(
                'Solo Parent ID year of issuance must be between %d and %d.',
                self::EARLIEST_YEAR,
                (int) date('Y'),
            ));
        }
    }
}
