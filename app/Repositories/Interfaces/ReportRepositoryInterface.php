<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Builder;

interface ReportRepositoryInterface
{
    /**
     * Lookup options for a category's type/search dropdown.
     * Matches the household search dropdown cap of 5 results.
     *
     * @return list<array{id: int, label: string}>
     */
    public function getCategoryOptions(string $categoryKey, ?string $search = null): array;

    /**
     * Labels for the selected lookup ids, in the given id order.
     *
     * @param  list<int>  $ids
     * @return list<array{id: int, label: string}>
     */
    public function filterLabels(string $categoryKey, array $ids): array;

    /**
     * Household or resident query for the category filter and selected columns.
     * The query is not executed.
     *
     * @param  array{mode?: string, ids?: list<int>}  $filter
     * @param  list<string>  $selectedColumns
     */
    public function buildReportQuery(string $categoryKey, array $filter, array $selectedColumns): Builder;
}
