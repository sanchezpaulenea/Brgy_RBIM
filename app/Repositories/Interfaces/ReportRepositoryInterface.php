<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface ReportRepositoryInterface
{
    /**
     * Lookup options for one sub-filter. Per-one search is capped at 5.
     * Checkbox lists pass $all so every match is returned.
     *
     * @return list<array{id: int, label: string}>
     */
    public function getCategoryOptions(string $categoryKey, string $filterKey, ?string $search = null, bool $all = false, ?string $mode = null): array;

    /**
     * Labels for the selected lookup ids, in the given id order.
     *
     * @param  list<int>  $ids
     * @return list<array{id: int, label: string}>
     */
    public function filterLabels(string $categoryKey, string $filterKey, array $ids): array;

    /**
     * Household or resident query for the category filters and selected columns.
     * The query is not executed. Joins to household_questions and lookup tables
     * are left joins.
     *
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $selectedColumns
     */
    public function buildReportQuery(string $categoryKey, array $filters, array $selectedColumns): Builder;

    /**
     * Households that have no household_questions row.
     */
    public function unansweredHouseholdCount(): int;
}
