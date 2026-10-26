<?php

namespace App\Repositories\Interfaces;

interface DashboardRepositoryInterface
{
    /**
     * @return array{households: int, residents: int, clans: int, streets: int}
     */
    public function summaryCounts(): array;

    /**
     * Households grouped by a lookup column on the household table.
     *
     * @return list<array{id: ?int, label: ?string, count: int}>
     */
    public function householdsByLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array;

    /**
     * Households grouped by a lookup column on their latest household_questions
     * row. Households with no questions row are returned under a null label.
     *
     * @return list<array{id: ?int, label: ?string, count: int}>
     */
    public function householdsByQuestionLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array;

    /**
     * Households grouped by the pair of housing unit and lot ownership on their
     * latest household_questions row.
     *
     * @return list<array{unit: ?string, lot: ?string, count: int}>
     */
    public function householdsByOwnership(): array;

    /**
     * Households grouped by a numeric household column. A null column counts
     * as $nullAs when given.
     *
     * @return list<array{value: ?int, count: int}>
     */
    public function householdsByNumber(string $column, ?int $nullAs = null): array;

    /**
     * Junction rows on each household's latest household_questions row,
     * grouped by lookup value.
     *
     * @return list<array{id: ?int, label: ?string, count: int}>
     */
    public function questionJunctionCounts(string $junctionTable, string $junctionId, string $lookupTable, string $lookupId, string $lookupLabel): array;

    /**
     * Residents grouped by a lookup column on the resident table.
     *
     * @return list<array{id: ?int, label: ?string, count: int}>
     */
    public function residentsByLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array;

    /**
     * Yes/No answers on the latest household questionnaire, plus the matching
     * household list for each side.
     *
     * @return list<array{key: string, count: int, items: list<array{id: int, name: string}>}>
     */
    public function householdsByQuestionBoolean(string $column): array;

    /**
     * Households that do or do not have a related presence row on their latest
     * questionnaire.
     *
     * @return list<array{key: string, count: int, items: list<array{id: int, name: string}>}>
     */
    public function householdsByPresence(string $table, string $owner): array;

    /**
     * Pets grouped by a lookup column.
     *
     * @return list<array{id: ?int, label: ?string, count: int}>
     */
    public function petsByLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array;

    /**
     * Residents grouped by a lookup on a one-to-one child table.
     *
     * @return list<array{id: ?int, label: ?string, count: int}>
     */
    public function residentsByChildLookup(
        string $childTable,
        string $fk,
        string $lookupTable,
        string $lookupId,
        string $lookupLabel,
        ?string $audience = null,
        ?array $via = null,
    ): array;

    /**
     * Residents grouped by a boolean column on a child table.
     *
     * @return list<array{key: string, count: int, items: list<array{id: int, name: string}>}>
     */
    public function residentsByChildBoolean(string $childTable, string $column, ?string $audience = null): array;

    /**
     * Residents grouped by whether a child-table column has a value.
     *
     * @return list<array{key: string, count: int, items: list<array{id: int, name: string}>}>
     */
    public function residentsByColumnPresence(string $childTable, string $column, ?string $audience = null): array;

    /**
     * Residents grouped by a text or enum column on a child table.
     *
     * @return list<array{id: ?int, label: ?string, count: int}>
     */
    public function residentsByChildColumn(string $childTable, string $column, ?string $audience = null): array;

    /**
     * Residents grouped by completed years of age.
     *
     * @param  list<array{label: string, min: int, max: int}>  $buckets
     * @return list<array{label: ?string, count: int, min?: int, max?: int}>
     */
    public function residentsByAgeBuckets(array $buckets): array;

    /**
     * Residents grouped by a numeric child-table column.
     *
     * @param  list<array{label: string, min: int|float|null, max: int|float|null}>  $buckets
     * @return list<array{label: ?string, count: int, min?: int|float, max?: int|float}>
     */
    public function residentsByChildNumberBuckets(
        string $childTable,
        string $column,
        array $buckets,
        ?string $audience = null,
        ?array $via = null,
    ): array;

    /**
     * Pets grouped by a yes/no column, with the owning household listed.
     *
     * @return list<array{key: string, count: int, items: list<array{id: int, name: string}>}>
     */
    public function petsByBoolean(string $column): array;

    /**
     * Pets grouped by rabies vaccination recency.
     *
     * @return list<array{id: ?int, label: ?string, count: int, choice?: string}>
     */
    public function petsByRabies(): array;

    /**
     * Residents grouped by a lookup sentinel: with (not sentinel) vs without.
     *
     * @return list<array{key: string, count: int, items: list<array{id: int, name: string}>}>
     */
    public function residentsBySentinel(string $childTable, string $fk, ?int $sentinelId, ?string $audience = null): array;

    /**
     * Residents 18+ grouped by CTC: none, issued here, issued elsewhere.
     *
     * @return list<array{id: ?int, label: ?string, count: int, choice?: string}>
     */
    public function residentsByCtcStatus(): array;

    /**
     * Yes / No / Undecided (null) on a child-table boolean.
     *
     * @return list<array{id: ?int, label: ?string, count: int, choice?: string}>
     */
    public function residentsByTriState(
        string $childTable,
        string $column,
        ?string $audience = null,
        ?array $via = null,
    ): array;

    /**
     * Residents grouped by barangay voter status: none, local, other.
     *
     * @return list<array{id: ?int, label: ?string, count: int, choice?: string}>
     */
    public function residentsByVoterStatus(): array;

    /**
     * Residents grouped by a distinct child-table label, using the lowest
     * matching id so report lookups can resolve the same label.
     *
     * @return list<array{id: ?int, label: ?string, count: int}>
     */
    public function residentsByChildDistinct(
        string $childTable,
        string $column,
        string $idColumn,
        ?string $audience = null,
    ): array;

    /**
     * Residents grouped by disability sentinel: with vs without.
     *
     * @return list<array{key: string, count: int, items: list<array{id: int, name: string}>}>
     */
    public function residentsByDisability(): array;
}
