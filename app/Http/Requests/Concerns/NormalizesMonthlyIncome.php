<?php

namespace App\Http\Requests\Concerns;

trait NormalizesMonthlyIncome
{
    /**
     * The form sends a formatted amount such as "12,500.00". Strip the
     * thousands separators so the value lands on the decimal(10,2) column
     * as a plain number.
     */
    protected function mergeNormalizedMonthlyIncome(string $field = 'monthly_income'): void
    {
        if (! $this->exists($field)) {
            return;
        }

        $value = $this->input($field);

        if (! is_string($value)) {
            return;
        }

        $trimmed = str_replace([',', ' '], '', trim($value));

        $this->merge([$field => $trimmed === '' ? null : $trimmed]);
    }
}
