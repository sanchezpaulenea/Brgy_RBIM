<?php

namespace App\Repositories;

use App\Models\ResidentManagement\Demographic\Sex;
use App\Models\ResidentManagement\Health\Disability;
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

    public function householdsByQuestionBoolean(string $column): array
    {
        $rows = $this->householdsWithLatestQuestions()
            ->leftJoin('resident as head', 'head.resident_id', '=', 'household.head_resident_id')
            ->select('household.household_id as id', 'hq.'.$column.' as value')
            ->selectRaw($this->personNameSql('head').' as name')
            ->orderBy('household.household_id')
            ->get();

        return $this->booleanGroups($rows);
    }

    public function householdsByPresence(string $table, string $owner): array
    {
        $rows = $this->householdsWithLatestQuestions()
            ->leftJoin($table.' as presence', 'presence.'.$owner, '=', 'hq.household_questions_id')
            ->leftJoin('resident as head', 'head.resident_id', '=', 'household.head_resident_id')
            ->whereNotNull('hq.household_questions_id')
            ->select('household.household_id as id')
            ->selectRaw('max(presence.'.$owner.') as value')
            ->selectRaw($this->personNameSql('head').' as name')
            ->groupBy('household.household_id', 'head.last_name', 'head.first_name', 'head.middle_name', 'head.suffix')
            ->orderBy('household.household_id')
            ->get()
            ->map(fn (object $row): object => (object) [
                'id' => $row->id,
                'name' => $row->name,
                'value' => $row->value !== null ? 1 : 0,
            ]);

        return $this->booleanGroups($rows, unanswered: false);
    }

    public function petsByLookup(string $column, string $lookupTable, string $lookupId, string $lookupLabel): array
    {
        $query = DB::table('pet_census')
            ->leftJoin($lookupTable.' as lk', 'lk.'.$lookupId, '=', 'pet_census.'.$column);

        return $this->labelCounts($query, $lookupId, $lookupLabel);
    }

    public function residentsByChildLookup(
        string $childTable,
        string $fk,
        string $lookupTable,
        string $lookupId,
        string $lookupLabel,
        ?string $audience = null,
        ?array $via = null,
    ): array {
        $query = $this->residentsWithChild($childTable, $via);
        $this->applyAudience($query, $audience);
        $query->leftJoin($lookupTable.' as lk', 'lk.'.$lookupId, '=', $childTable.'.'.$fk);

        return $this->labelCounts($query, $lookupId, $lookupLabel);
    }

    public function residentsByChildBoolean(string $childTable, string $column, ?string $audience = null): array
    {
        $query = DB::table('resident')
            ->leftJoin($childTable, $childTable.'.resident_id', '=', 'resident.resident_id')
            ->select('resident.resident_id as id', $childTable.'.'.$column.' as value')
            ->selectRaw($this->personNameSql('resident').' as name')
            ->orderBy('resident.resident_id');

        $this->applyAudience($query, $audience);

        return $this->booleanGroups($query->get());
    }

    public function residentsByColumnPresence(string $childTable, string $column, ?string $audience = null): array
    {
        $qualified = $childTable.'.'.$column;
        $query = DB::table('resident')
            ->leftJoin($childTable, $childTable.'.resident_id', '=', 'resident.resident_id')
            ->select('resident.resident_id as id')
            ->selectRaw('case when '.$qualified.' is null or trim('.$qualified.') = \'\' then 0 else 1 end as value')
            ->selectRaw($this->personNameSql('resident').' as name')
            ->orderBy('resident.resident_id');

        $this->applyAudience($query, $audience);

        return $this->booleanGroups($query->get(), unanswered: false);
    }

    public function residentsByChildColumn(string $childTable, string $column, ?string $audience = null): array
    {
        $query = DB::table('resident')
            ->leftJoin($childTable, $childTable.'.resident_id', '=', 'resident.resident_id');

        $this->applyAudience($query, $audience);

        return $query
            ->select($childTable.'.'.$column.' as label')
            ->selectRaw('count(*) as total')
            ->groupBy($childTable.'.'.$column)
            ->get()
            ->map(fn (object $row): array => [
                'id' => null,
                'label' => $row->label !== null && trim((string) $row->label) !== '' ? (string) $row->label : null,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    public function petsByBoolean(string $column): array
    {
        $rows = DB::table('pet_census')
            ->join('household', 'household.household_id', '=', 'pet_census.household_id')
            ->leftJoin('resident as head', 'head.resident_id', '=', 'household.head_resident_id')
            ->select('household.household_id as id', 'pet_census.'.$column.' as value')
            ->selectRaw($this->personNameSql('head').' as name')
            ->orderBy('household.household_id')
            ->get();

        return $this->booleanGroups($rows, unanswered: false);
    }

    public function petsByRabies(): array
    {
        $cutoff = now()->subMonths(12)->toDateString();
        $date = 'pet_census.rabies_vaccination_date';
        $case = "case
                when {$date} is null then 'No vaccination record'
                when {$date} >= '{$cutoff}' then 'Vaccinated within last 12 months'
                else 'Vaccination older than 12 months'
            end";

        return DB::table('pet_census')
            ->selectRaw($case.' as label, count(*) as total')
            ->groupByRaw($case)
            ->get()
            ->map(fn (object $row): array => [
                'id' => null,
                'label' => (string) $row->label,
                'count' => (int) $row->total,
                'choice' => match ((string) $row->label) {
                    'Vaccinated within last 12 months' => 'within_12',
                    'Vaccination older than 12 months' => 'older_12',
                    default => null,
                },
            ])
            ->all();
    }

    public function residentsBySentinel(string $childTable, string $fk, ?int $sentinelId, ?string $audience = null): array
    {
        $query = DB::table('resident')
            ->leftJoin($childTable, $childTable.'.resident_id', '=', 'resident.resident_id')
            ->select('resident.resident_id as id')
            ->selectRaw(
                $sentinelId === null
                    ? 'case when '.$childTable.'.'.$fk.' is null then 0 else 1 end as value'
                    : 'case when '.$childTable.'.'.$fk.' is null or '.$childTable.'.'.$fk.' = ? then 0 else 1 end as value',
                $sentinelId === null ? [] : [$sentinelId],
            )
            ->selectRaw($this->personNameSql('resident').' as name')
            ->orderBy('resident.resident_id');

        $this->applyAudience($query, $audience);

        return $this->booleanGroups($query->get(), unanswered: false);
    }

    public function residentsByCtcStatus(): array
    {
        $query = DB::table('resident')
            ->leftJoin('community_tax_cert as ctc', 'ctc.resident_id', '=', 'resident.resident_id');

        $this->applyAudience($query, 'ctc');

        $case = "case
                when ctc.has_valid_ctc = 1 and ctc.ctc_issued_here = 1 then 'Yes, issued in this barangay'
                when ctc.has_valid_ctc = 1 then 'Yes, not issued in this barangay'
                else 'No'
            end";

        return $query
            ->selectRaw($case.' as label, count(*) as total')
            ->groupByRaw($case)
            ->get()
            ->map(fn (object $row): array => [
                'id' => null,
                'label' => (string) $row->label,
                'count' => (int) $row->total,
                'choice' => match ((string) $row->label) {
                    'Yes, issued in this barangay' => 'here',
                    'Yes, not issued in this barangay' => 'other',
                    default => 'no',
                },
            ])
            ->all();
    }

    public function residentsByTriState(
        string $childTable,
        string $column,
        ?string $audience = null,
        ?array $via = null,
    ): array {
        $query = $this->residentsWithChild($childTable, $via);
        $this->applyAudience($query, $audience);

        $qualified = $childTable.'.'.$column;
        $case = "case
                when {$qualified} = 1 then 'Have Intention'
                when {$qualified} = 0 then 'No Intention'
                else 'Undecided'
            end";

        return $query
            ->selectRaw($case.' as label, count(*) as total')
            ->groupByRaw($case)
            ->get()
            ->map(fn (object $row): array => [
                'id' => null,
                'label' => (string) $row->label,
                'count' => (int) $row->total,
                'choice' => match ((string) $row->label) {
                    'Have Intention' => 'yes',
                    'No Intention' => 'no',
                    default => 'undecided',
                },
            ])
            ->all();
    }

    public function residentsByVoterStatus(): array
    {
        $local = str_replace("'", "''", $this->localBarangayKey());
        $value = 'sociocivic.registered_barangay_voter';
        $normalized = 'trim(lower(case when lower(trim('.$value.')) like \'barangay %\' then substring(trim('.$value.'), 10) else trim('.$value.') end))';
        $registered = $value.' is not null and trim('.$value.') <> \'\' and lower(trim('.$value.')) not in (\'yes\', \'no\')';
        $case = "case
                when {$registered} and {$normalized} = '{$local}' then 'Yes, registered in this barangay'
                when {$registered} then 'Yes, registered in a different barangay'
                else 'No'
            end";

        return DB::table('resident')
            ->leftJoin('sociocivic', 'sociocivic.resident_id', '=', 'resident.resident_id')
            ->selectRaw($case.' as label, count(*) as total')
            ->groupByRaw($case)
            ->get()
            ->map(fn (object $row): array => [
                'id' => null,
                'label' => (string) $row->label,
                'count' => (int) $row->total,
                'choice' => match ((string) $row->label) {
                    'Yes, registered in this barangay' => 'local',
                    'Yes, registered in a different barangay' => 'other',
                    default => 'no',
                },
            ])
            ->all();
    }

    public function residentsByChildDistinct(
        string $childTable,
        string $column,
        string $idColumn,
        ?string $audience = null,
    ): array {
        $query = $this->residentsWithChild($childTable, null);
        $this->applyAudience($query, $audience);

        return $query
            ->selectRaw('min('.$childTable.'.'.$idColumn.') as id, '.$childTable.'.'.$column.' as label, count(*) as total')
            ->groupBy($childTable.'.'.$column)
            ->get()
            ->map(fn (object $row): array => [
                'id' => $row->id !== null ? (int) $row->id : null,
                'label' => $row->label !== null && trim((string) $row->label) !== '' ? (string) $row->label : null,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    public function residentsByDisability(): array
    {
        return $this->residentsBySentinel('health', 'disability_id', Disability::noneId());
    }

    public function residentsByAgeBuckets(array $buckets): array
    {
        $expression = 'timestampdiff(YEAR, `resident`.`date_of_birth`, curdate())';
        $rows = DB::table('resident')
            ->selectRaw($expression.' as value, count(*) as total')
            ->groupByRaw($expression)
            ->get();

        return $this->bucketCounts($rows, $buckets);
    }

    public function residentsByChildNumberBuckets(
        string $childTable,
        string $column,
        array $buckets,
        ?string $audience = null,
        ?array $via = null,
    ): array {
        $query = $this->residentsWithChild($childTable, $via);
        $this->applyAudience($query, $audience);

        $qualified = $childTable.'.'.$column;
        $rows = $query
            ->selectRaw($qualified.' as value, count(*) as total')
            ->groupByRaw($qualified)
            ->get();

        return $this->bucketCounts($rows, $buckets);
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
     * @param  array{table: string, from: string, to: string, owner: string}|null  $via
     */
    private function residentsWithChild(string $childTable, ?array $via): QueryBuilder
    {
        $query = DB::table('resident');

        if ($via === null) {
            return $query->leftJoin($childTable, $childTable.'.resident_id', '=', 'resident.resident_id');
        }

        return $query
            ->leftJoin($via['table'], $via['table'].'.'.$via['owner'], '=', 'resident.resident_id')
            ->leftJoin($childTable, $childTable.'.'.$via['from'], '=', $via['table'].'.'.$via['to']);
    }

    private function applyAudience(QueryBuilder $query, ?string $audience): void
    {
        if ($audience === 'infant') {
            $query->whereRaw('timestampdiff(MONTH, `resident`.`date_of_birth`, curdate()) between 0 and 11');

            return;
        }

        if ($audience === 'women') {
            $query->where('resident.sex_id', Sex::FEMALE)
                ->whereRaw('timestampdiff(YEAR, `resident`.`date_of_birth`, curdate()) between 10 and 54');

            return;
        }

        if ($audience === 'ctc') {
            $query->whereRaw('timestampdiff(YEAR, `resident`.`date_of_birth`, curdate()) >= 18');

            return;
        }

        if ($audience === 'skills') {
            $query->whereRaw('timestampdiff(YEAR, `resident`.`date_of_birth`, curdate()) >= 15');
        }
    }

    private function localBarangayKey(): string
    {
        $value = DB::table('system_setting')
            ->where('setting_key', 'barangay_name')
            ->value('setting_value');
        $value = mb_strtolower(trim((string) $value));
        $value = preg_replace('/^barangay\s+/u', '', $value) ?? $value;
        $value = trim((string) preg_replace('/\s+/u', ' ', (string) $value));

        return $value !== '' ? $value : 'happy hallow';
    }

    /**
     * @return list<array{id: ?int, label: ?string, count: int}>
     */
    private function labelCounts(QueryBuilder $query, string $lookupId, string $lookupLabel): array
    {
        /** @var Collection<int, object> $rows */
        $rows = $query
            ->select('lk.'.$lookupId.' as id', 'lk.'.$lookupLabel.' as label')
            ->selectRaw('count(*) as total')
            ->groupBy('lk.'.$lookupId, 'lk.'.$lookupLabel)
            ->get();

        return $rows
            ->map(fn (object $row): array => [
                'id' => $row->id !== null ? (int) $row->id : null,
                'label' => $row->label !== null ? (string) $row->label : null,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @param  Collection<int, object>|\Illuminate\Support\LazyCollection<int, object>  $rows
     * @return list<array{key: string, count: int, items: list<array{id: int, name: string}>}>
     */
    private function booleanGroups(iterable $rows, bool $unanswered = true): array
    {
        $groups = [
            'yes' => ['key' => 'yes', 'count' => 0, 'items' => []],
            'no' => ['key' => 'no', 'count' => 0, 'items' => []],
        ];

        if ($unanswered) {
            $groups['missing'] = ['key' => 'missing', 'count' => 0, 'items' => []];
        }

        foreach ($rows as $row) {
            $value = $row->value ?? null;
            $key = match (true) {
                $value === null || $value === '' => $unanswered ? 'missing' : 'no',
                (int) $value === 1 => 'yes',
                default => 'no',
            };

            $name = trim((string) ($row->name ?? ''));
            $groups[$key]['count']++;
            $groups[$key]['items'][] = [
                'id' => (int) $row->id,
                'name' => $name !== '' ? $name : 'Record #'.$row->id,
            ];
        }

        return array_values($groups);
    }

    /**
     * @param  Collection<int, object>  $rows
     * @param  list<array{label: string, min: int|float|null, max: int|float|null}>  $buckets
     * @return list<array{label: ?string, count: int, min?: int|float, max?: int|float}>
     */
    private function bucketCounts(Collection $rows, array $buckets): array
    {
        $counts = array_fill(0, count($buckets), 0);
        $missing = 0;

        foreach ($rows as $row) {
            if ($row->value === null || $row->value === '') {
                $missing += (int) $row->total;

                continue;
            }

            $value = is_numeric($row->value) ? (float) $row->value : null;

            if ($value === null) {
                $missing += (int) $row->total;

                continue;
            }

            foreach ($buckets as $index => $bucket) {
                $min = $bucket['min'] ?? null;
                $max = $bucket['max'] ?? null;

                if (($min === null || $value >= (float) $min) && ($max === null || $value <= (float) $max)) {
                    $counts[$index] += (int) $row->total;

                    break;
                }
            }
        }

        $result = [];

        foreach ($buckets as $index => $bucket) {
            if ($counts[$index] <= 0) {
                continue;
            }

            $result[] = [
                'label' => $bucket['label'],
                'count' => $counts[$index],
                'min' => $bucket['min'] ?? null,
                'max' => $bucket['max'] ?? null,
            ];
        }

        if ($missing > 0) {
            $result[] = ['label' => null, 'count' => $missing];
        }

        return $result;
    }

    private function personNameSql(string $alias): string
    {
        $last = $alias.'.last_name';
        $first = $alias.'.first_name';
        $middle = $alias.'.middle_name';
        $suffix = $alias.'.suffix';

        return "trim(concat(coalesce({$last}, ''), case when coalesce({$last}, '') = '' then '' else ', ' end, trim(concat_ws(' ', {$first}, {$middle}, {$suffix}))))";
    }
}
