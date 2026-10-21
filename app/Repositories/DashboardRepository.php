<?php

namespace App\Repositories;

use App\Repositories\Interfaces\DashboardRepositoryInterface;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function summaryCounts(): array
    {
        return [
            'households' => DB::table('household')->count(),
            'residents' => DB::table('resident')->count(),
            'clans' => DB::table('clan')->count(),
            'streets' => DB::table('street')->count(),
        ];
    }

    public function householdsByLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array
    {
        $query = DB::table('household')
            ->leftJoin($lookupTable.' as lk', 'lk.'.$lookupId, '=', 'household.'.$column);

        return $this->labelCounts($query, $lookupId, $lookupLabel);
    }

    public function householdsByQuestionLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array
    {
        $query = $this->householdsWithLatestQuestions()
            ->leftJoin($lookupTable.' as lk', 'lk.'.$lookupId, '=', 'hq.'.$column);

        return $this->labelCounts($query, $lookupId, $lookupLabel);
    }

    public function householdsByOwnership(): array
    {
        return $this->householdsWithLatestQuestions()
            ->leftJoin('ownership_type as unit_type', 'unit_type.ownership_type_id', '=', 'hq.ownership_of_housing_unit_id')
            ->leftJoin('ownership_type as lot_type', 'lot_type.ownership_type_id', '=', 'hq.ownership_of_lot_id')
            ->select('unit_type.ownership_type as unit', 'lot_type.ownership_type as lot')
            ->selectRaw('count(*) as total')
            ->groupBy('unit_type.ownership_type_id', 'unit_type.ownership_type', 'lot_type.ownership_type_id', 'lot_type.ownership_type')
            ->get()
            ->map(fn (object $row): array => [
                'unit' => $row->unit !== null ? (string) $row->unit : null,
                'lot' => $row->lot !== null ? (string) $row->lot : null,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    public function householdsByNumber(string $column, ?int $nullAs = null): array
    {
        $wrapped = DB::getQueryGrammar()->wrap('household.'.$column);
        $expression = $nullAs === null ? $wrapped : 'coalesce('.$wrapped.', '.$nullAs.')';

        return DB::table('household')
            ->selectRaw($expression.' as value, count(*) as total')
            ->groupByRaw($expression)
            ->get()
            ->map(fn (object $row): array => [
                'value' => $row->value !== null ? (int) $row->value : null,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    public function questionJunctionCounts(string $junctionTable, string $junctionId, string $lookupTable, string $lookupId, string $lookupLabel): array
    {
        $query = DB::table($junctionTable.' as j')
            ->joinSub($this->latestQuestionIds(), 'latest', 'latest.latest_id', '=', 'j.household_question_id')
            ->leftJoin($lookupTable.' as lk', 'lk.'.$lookupId, '=', 'j.'.$junctionId);

        return $this->labelCounts($query, $lookupId, $lookupLabel);
    }

    public function residentsByLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array
    {
        $query = DB::table('resident')
            ->leftJoin($lookupTable.' as lk', 'lk.'.$lookupId, '=', 'resident.'.$column);

        return $this->labelCounts($query, $lookupId, $lookupLabel);
    }

    /**
     * Every household with its latest household_questions row as `hq`, the
     * same row Report Generation reads. Unanswered households keep a null row.
     */
    private function householdsWithLatestQuestions(): QueryBuilder
    {
        return DB::table('household')
            ->leftJoinSub($this->latestQuestionIds(), 'latest', 'latest.household_id', '=', 'household.household_id')
            ->leftJoin('household_questions as hq', 'hq.household_questions_id', '=', 'latest.latest_id');
    }

    private function latestQuestionIds(): QueryBuilder
    {
        return DB::table('household_questions')
            ->selectRaw('household_id, max(household_questions_id) as latest_id')
            ->groupBy('household_id');
    }

    /**
     * @return list<array{label: ?string, count: int}>
     */
    private function labelCounts(QueryBuilder $query, string $lookupId, string $lookupLabel): array
    {
        /** @var Collection<int, object> $rows */
        $rows = $query
            ->select('lk.'.$lookupLabel.' as label')
            ->selectRaw('count(*) as total')
            ->groupBy('lk.'.$lookupId, 'lk.'.$lookupLabel)
            ->get();

        return $rows
            ->map(fn (object $row): array => [
                'label' => $row->label !== null ? (string) $row->label : null,
                'count' => (int) $row->total,
            ])
            ->all();
    }
}
