<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPlaceName implements ValidationRule
{
    public const PATTERN = '/^\p{L}[\p{L}\d .,\'\-]*$/u';

    public const MIN_LENGTH = 3;

    public function __construct(private readonly string $label) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail("{$this->label} must be text.");

            return;
        }

        $name = trim($value);

        if (mb_strlen($name) < self::MIN_LENGTH) {
            $fail("{$this->label} must be at least ".self::MIN_LENGTH.' characters long.');

            return;
        }

        if (preg_match(self::PATTERN, $name) !== 1) {
            $fail("{$this->label} must start with a letter and may only contain letters, numbers, spaces, periods, commas, hyphens, and apostrophes.");

            return;
        }

        if (preg_match_all('/\p{L}/u', $name) < self::MIN_LENGTH) {
            $fail("{$this->label} must contain at least ".self::MIN_LENGTH.' letters.');
        }
    }
}
