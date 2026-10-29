<?php

namespace App\Models\Concerns;

use App\Support\IdenticalWriteLock;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

trait FindsOrCreatesLookupByLabel
{
    public static function findOrCreateByLabel(string $label): static
    {
        $column = static::lookupLabelColumn();
        $normalized = Str::of($label)->squish()->title()->toString();

        return IdenticalWriteLock::acquire(static::class.':'.Str::lower($normalized), function () use ($column, $normalized) {
            $existing = static::query()
                ->whereRaw('LOWER('.$column.') = ?', [Str::lower($normalized)])
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            try {
                return static::query()->create([$column => $normalized]);
            } catch (UniqueConstraintViolationException) {
                return static::query()
                    ->whereRaw('LOWER('.$column.') = ?', [Str::lower($normalized)])
                    ->firstOrFail();
            }
        });
    }

    protected static function lookupLabelColumn(): string
    {
        return (new static)->getFillable()[0];
    }
}
