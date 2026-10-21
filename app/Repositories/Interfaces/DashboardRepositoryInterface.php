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
     * @return list<array{label: ?string, count: int}>
     */
    public function householdsByLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array;

    /**
     * Households grouped by a lookup column on their latest household_questions
     * row. Households with no questions row are returned under a null label.
     *
     * @return list<array{label: ?string, count: int}>
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
     * @return list<array{label: ?string, count: int}>
     */
    public function questionJunctionCounts(string $junctionTable, string $junctionId, string $lookupTable, string $lookupId, string $lookupLabel): array;

    /**
     * Residents grouped by a lookup column on the resident table.
     *
     * @return list<array{label: ?string, count: int}>
     */
    public function residentsByLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array;
}
