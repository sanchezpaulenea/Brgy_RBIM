<?php

namespace App\Services\ResidentManagement\Migration;

use App\Models\ResidentManagement\Demographic\ResidentType;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class MigrationClassifier
{
    public const MIGRANT_THRESHOLD_MONTHS = 6;

    /**
     * Standardize a place name for storage and comparison.
     */
    public static function normalizePlace(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        if ($normalized === '') {
            return null;
        }

        $normalized = mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
        $comparable = self::comparablePlace($normalized);

        return $comparable === '' ? null : $normalized;
    }

    public static function placesMatch(mixed $left, mixed $right): bool
    {
        $leftComparable = self::comparablePlace($left);
        $rightComparable = self::comparablePlace($right);

        return $leftComparable !== '' && $leftComparable === $rightComparable;
    }

    public static function sameAsCurrentResidence(
        mixed $previousBrgy,
        mixed $previousCity,
        mixed $currentBrgy,
        mixed $currentCity,
    ): bool {
        return self::placesMatch($previousBrgy, $currentBrgy)
            && self::placesMatch($previousCity, $currentCity);
    }

    public static function lengthOfStayMonths(mixed $transferDate, ?CarbonInterface $asOf = null): ?int
    {
        $parsed = self::parseTransferDate($transferDate);

        if ($parsed === null) {
            return null;
        }

        $asOf ??= Carbon::now();
        $months = $parsed->copy()->startOfMonth()->diffInMonths($asOf->copy()->startOfMonth());

        return max(0, (int) $months);
    }

    public static function lengthOfStayLabel(?int $months): ?string
    {
        if ($months === null) {
            return null;
        }

        $years = intdiv($months, 12);
        $remainingMonths = $months % 12;

        return $years.' years / '.$remainingMonths.' months';
    }

    /**
     * Non-migrant when previous and current barangay + city match.
     * Otherwise migrant at 6 months of stay, transient below that.
     */
    public static function classify(bool $sameAddress, ?int $stayMonths): int
    {
        if ($sameAddress) {
            return ResidentType::NON_MIGRANT;
        }

        if ($stayMonths !== null && $stayMonths >= self::MIGRANT_THRESHOLD_MONTHS) {
            return ResidentType::MIGRANT;
        }

        return ResidentType::TRANSIENT;
    }

    public static function isNonMigrant(int $residentTypeId): bool
    {
        return $residentTypeId === ResidentType::NON_MIGRANT;
    }

    public static function parseTransferDate(mixed $value): ?Carbon
    {
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value)->startOfMonth();
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        try {
            if (preg_match('/^\d{4}-\d{2}$/', $value) === 1) {
                return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
            }

            return Carbon::parse($value)->startOfMonth();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function normalizeTransferDate(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $parsed = self::parseTransferDate($value);

        return $parsed?->toDateString() ?? $value;
    }

    private static function comparablePlace(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        $normalized = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? ''));

        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/\s+(city|municipality)$/u', '', $normalized) ?? $normalized;

        return trim($normalized);
    }
}
