<?php

namespace App\Http\Requests\HouseholdManagement\Concerns;

use App\Models\HouseholdManagement\Household;
use Illuminate\Support\Str;

trait NormalizesHouseholdAddress
{
    /**
     * @param  list<string>  $fields
     */
    protected function mergeNormalizedAddressFields(array $fields): void
    {
        $merge = [];

        foreach ($fields as $field) {
            if ($this->exists($field)) {
                $merge[$field] = $this->normalizeOptionalAddressText($this->input($field));
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    protected function normalizeOptionalAddressText(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $formatted = Str::of($value)->squish()->toString();

        if ($formatted === '' || $this->isNotApplicableAddress($formatted)) {
            return null;
        }

        return $formatted;
    }

    protected function isNotApplicableAddress(string $value): bool
    {
        return (bool) preg_match('/^(n\/?a|n\.a\.?|not applicable)$/i', $value);
    }

    protected function mergeBasementLevelFromAnswer(): void
    {
        if (! $this->exists('has_basement')) {
            return;
        }

        if (! $this->boolean('has_basement')) {
            $this->merge([
                'number_of_basement_level' => 0,
            ]);
        }
    }

    protected function householdFromRoute(): ?Household
    {
        $household = $this->route('household');

        return $household instanceof Household ? $household : null;
    }
}
