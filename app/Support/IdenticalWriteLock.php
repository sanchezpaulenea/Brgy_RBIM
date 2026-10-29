<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Serializes two accounts saving the same values.
 *
 * A row lock taken inside a transaction is not enough on MySQL. The waiting
 * transaction can keep a snapshot from before the first account commits, so
 * its "does this already exist?" check still sees nothing and inserts a second
 * row. Named locks are taken before that transaction starts, so the second
 * account checks only after the first save is visible.
 */
class IdenticalWriteLock
{
    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function acquire(string $name, callable $callback): mixed
    {
        if (! self::supportsNamedLocks()) {
            return $callback();
        }

        $lockName = 'rbim:'.substr(hash('sha256', $name), 0, 48);
        $row = DB::selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$lockName]);

        if ((int) ($row->acquired ?? 0) !== 1) {
            throw ValidationException::withMessages([
                'record' => ['Another account is saving this same record. Please try again.'],
            ]);
        }

        try {
            return $callback();
        } finally {
            DB::select('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function resident(array $data): string
    {
        return self::key('resident', [
            $data['last_name'] ?? '',
            $data['first_name'] ?? '',
            $data['middle_name'] ?? '',
            $data['suffix'] ?? '',
            substr((string) ($data['date_of_birth'] ?? ''), 0, 10),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function personnel(array $data): string
    {
        return self::key('personnel', [
            $data['personnel_last_name'] ?? '',
            $data['personnel_first_name'] ?? '',
            $data['personnel_middle_name'] ?? '',
            $data['personnel_suffix'] ?? '',
            substr((string) ($data['personnel_date_of_birth'] ?? ''), 0, 10),
        ]);
    }

    /**
     * @param  list<mixed>  $parts
     */
    private static function key(string $prefix, array $parts): string
    {
        $normalized = array_map(
            fn (mixed $part): string => mb_strtolower(trim((string) $part)),
            $parts,
        );

        return $prefix.':'.implode('|', $normalized);
    }

    private static function supportsNamedLocks(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
}
