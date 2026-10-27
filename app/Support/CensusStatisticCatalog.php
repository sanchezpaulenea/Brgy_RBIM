<?php

namespace App\Support;

use InvalidArgumentException;

class CensusStatisticCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        $statistics = config('census_statistics.statistics', []);

        return is_array($statistics) ? $statistics : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function visible(bool $hideSensitive): array
    {
        return array_filter(
            self::all(),
            fn (array $statistic): bool => ! $hideSensitive || empty($statistic['sensitive']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function find(string $id): array
    {
        $statistic = self::all()[$id] ?? null;

        if (! is_array($statistic)) {
            throw new InvalidArgumentException("Unknown census statistic [{$id}].");
        }

        return $statistic;
    }
}
