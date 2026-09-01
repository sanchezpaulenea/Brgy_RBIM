<?php

namespace App\Http\Requests\HouseholdManagement\Concerns;

use App\Models\HouseholdManagement\Household;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

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

    protected function validateUniqueLotAndBlock(Validator $validator, ?Household $existing = null): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        [$houseLot, $blockNum] = $this->lotAndBlockValues($existing);

        if (! is_string($houseLot) || ! is_string($blockNum) || $houseLot === '' || $blockNum === '') {
            return;
        }

        $query = Household::query()
            ->where('house_lot', $houseLot)
            ->where('block_num', $blockNum);

        if ($existing !== null) {
            $query->where('household_id', '!=', $existing->household_id);
        }

        if (! $query->exists()) {
            return;
        }

        $message = 'A household with this house/lot and block number already exists.';
        $validator->errors()->add('house_lot', $message);
        $validator->errors()->add('block_num', $message);
    }

    /**
     * @return array{0: mixed, 1: mixed}
     */
    protected function lotAndBlockValues(?Household $existing = null): array
    {
        $houseLot = $this->exists('house_lot') ? $this->input('house_lot') : $existing?->house_lot;
        $blockNum = $this->exists('block_num') ? $this->input('block_num') : $existing?->block_num;

        return [$houseLot, $blockNum];
    }

    protected function householdFromRoute(): ?Household
    {
        $household = $this->route('household');

        return $household instanceof Household ? $household : null;
    }
}
