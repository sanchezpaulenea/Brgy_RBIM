<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait ResolvesLookupSentinel
{
    public function indicatesNone(): bool
    {
        return (bool) preg_match('/^(none|n\/a|n\.a\.?|na)$/', $this->normalizedLookupLabel());
    }

    public function indicatesNotApplicable(): bool
    {
        return (bool) preg_match('/^(none|n\/a|n\.a\.?|na|not applicable)$/', $this->normalizedLookupLabel());
    }

    public static function noneId(): ?int
    {
        return static::firstSentinelId(fn (self $row) => $row->indicatesNone());
    }

    public static function notApplicableId(): ?int
    {
        return static::firstSentinelId(fn (self $row) => $row->indicatesNotApplicable());
    }

    /**
     * @param  callable(self): bool  $predicate
     */
    private static function firstSentinelId(callable $predicate): ?int
    {
        $match = static::query()
            ->orderBy((new static)->getKeyName())
            ->get()
            ->first($predicate);

        return $match?->getKey();
    }

    private function normalizedLookupLabel(): string
    {
        $column = method_exists(static::class, 'lookupLabelColumn')
            ? static::lookupLabelColumn()
            : ((new static)->getFillable()[0] ?? '');

        return Str::of((string) $this->getAttribute($column))->squish()->lower()->toString();
    }
}
