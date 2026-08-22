<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBarangayContactNumber implements ValidationRule
{
    public const MESSAGE = 'Please enter a valid Philippine contact number. Use an 11-digit mobile number starting with 09 (e.g. 09123456789) or +639XXXXXXXXX, or a landline with area code plus 7–8 local digits (e.g. 074-123-4567).';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(self::MESSAGE);

            return;
        }

        $normalized = preg_replace('/[\s\-\(\)]+/', '', $value) ?? '';

        if ($this->isMobile($normalized) || $this->isLandline($normalized)) {
            return;
        }

        $fail(self::MESSAGE);
    }

    private function isMobile(string $value): bool
    {
        return preg_match('/^09\d{9}$/', $value) === 1
            || preg_match('/^\+639\d{9}$/', $value) === 1;
    }

    private function isLandline(string $value): bool
    {
        return preg_match('/^02\d{7,8}$/', $value) === 1
            || preg_match('/^0[3-8]\d\d{7,8}$/', $value) === 1;
    }
}
