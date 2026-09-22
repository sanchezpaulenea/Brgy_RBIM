<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait FindsOrCreatesLookupByLabel
{
    public static function findOrCreateByLabel(string $label): static
    {
        $column = static::lookupLabelColumn();
        $normalized = Str::of($label)->squish()->title()->toString();

        $existing = static::query()
            ->whereRaw('LOWER('.$column.') = ?', [Str::lower($normalized)])
            ->first();

        return $existing ?? static::query()->create([$column => $normalized]);
    }

    protected static function lookupLabelColumn(): string
    {
        return (new static)->getFillable()[0];
    }
}
