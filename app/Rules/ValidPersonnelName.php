<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class ValidPersonnelName implements ValidationRule
{
    public const PATTERN = "/^[\\p{L} .'\\-]+$/u";

    /**
     * @var array<string, string>
     */
    private const LABELS = [
        'personnel_last_name' => 'Last name',
        'personnel_first_name' => 'First name',
        'personnel_middle_name' => 'Middle name',
        'personnel_suffix' => 'Suffix',
        'last_name' => 'Last name',
        'first_name' => 'First name',
        'middle_name' => 'Middle name',
        'suffix' => 'Suffix',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $field = Str::afterLast($attribute, '.');
        $label = self::LABELS[$field] ?? self::LABELS[$attribute] ?? 'Name';

        if (! is_string($value) || preg_match(self::PATTERN, $value) !== 1) {
            $fail("{$label} may only contain letters, spaces, hyphens, apostrophes, and periods.");

            return;
        }

        if (preg_match('/\p{L}/u', $value) !== 1) {
            $fail("{$label} must contain at least one letter.");
        }
    }
}
