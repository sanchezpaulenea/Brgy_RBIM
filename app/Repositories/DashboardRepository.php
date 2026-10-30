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
    /**
     * @var array{street: ?int, clan: ?int, age: ?string, sex: int|string|null}
     */
    private array $populationFilters = [
        'street' => null,
        'clan' => null,
        'age' => null,
        'sex' => null,
    ];

    /**
     * @var list<array<string, mixed>>
     */
    private array $attributeFilters = [];

    public function usePopulationFilters(array $filters): void
    {
        $this->populationFilters = [
            'street' => isset($filters['street']) ? (int) $filters['street'] : null,
            'clan' => isset($filters['clan']) ? (int) $filters['clan'] : null,
            'age' => isset($filters['age']) && $filters['age'] !== '' ? (string) $filters['age'] : null,
            'sex' => $filters['sex'] ?? null,
        ];
    }

    public function useAttributeFilters(array $filters): void
    {
        $this->attributeFilters = array_values($filters);
    }

    public function lookupOptions(string $table, string $idColumn, string $labelColumn): array
    {
        $allowed = [
            'household_status' => ['household_status_id', 'household_status'],
            'ownership_type' => ['ownership_type_id', 'ownership_type'],
            'fuel_type' => ['fuel_type_id', 'fuel_type'],
            'water_source' => ['water_source_id', 'water_source'],
            'kitchen_garbage_disposal' => ['kitchen_garbage_disposal_id', 'kitchen_garbage_disposal'],
            'toilet_facility_type' => ['toilet_facility_type_id', 'toilet_facility_type'],
            'building_house_type' => ['building_house_type_id', 'building_house_type'],
            'construction_material_outer_wall' => ['construction_material_outer_wall_id', 'construction_material_outer_wall'],
            'common_disease_cause_death_in_brgy' => ['common_disease_id', 'common_disease'],
            'primary_need_of_brgy' => ['primary_need_id', 'primary_need'],
            'specie' => ['specie_id', 'specie'],
            'sex' => ['sex_id', 'sex'],
        ];

        if (($allowed[$table] ?? null) !== [$idColumn, $labelColumn]) {
            return [];
        }

        return $this->optionRows($table, $idColumn, $labelColumn);
    }

    public function distinctValues(string $table, string $column): array
    {
        $allowed = [
            'household' => ['number_of_house_story', 'number_of_basement_level'],
        ];

        if (! in_array($column, $allowed[$table] ?? [], true)) {
            return [];
        }

        return DB::table($table)
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->all();
    }

    public function filteredHouseholdCount(): int
    {
        $query = DB::table('household');
        $this->applyHouseholdFilters($query);

        return (int) $query->count();
    }

    public function filteredResidentCount(): int
    {
        $query = DB::table('resident');
        $this->applyResidentFilters($query);

        return (int) $query->count();
    }

    public function populationFilterCatalog(): array
    {
        return [
            'streets' => $this->optionRows('street', 'street_id', 'street_name'),
            'clans' => $this->optionRows('clan', 'clan_id', 'clan_name'),
            'sexes' => $this->optionRows('sex', 'sex_id', 'sex'),
        ];
    }

    public function householdOverviewQuery(): QueryBuilder
    {
        $query = DB::table('household')
            ->leftJoin('street', 'street.street_id', '=', 'household.street_id')
            ->leftJoin('clan', 'clan.clan_id', '=', 'household.clan_id')
            ->leftJoin('resident as head', 'head.resident_id', '=', 'household.head_resident_id')
            ->select([
                'household.household_id',
                'household.house_number',
                'street.street_name',
                'clan.clan_name',
                'head.last_name',
                'head.first_name',
                'head.middle_name',
                'head.suffix',
            ])
            ->selectSub(
                DB::table('resident')
                    ->selectRaw('count(*)')
                    ->whereColumn('resident.household_id', 'household.household_id'),
                'member_count',
            );
        $this->applyHouseholdFilters($query);

        return $query;
    }

    public function residentOverviewQuery(): QueryBuilder
    {
        $query = DB::table('resident')
            ->leftJoin('household', 'household.household_id', '=', 'resident.household_id')
            ->leftJoin('street', 'street.street_id', '=', 'household.street_id')
            ->leftJoin('clan', 'clan.clan_id', '=', 'resident.clan_id')
            ->leftJoin('sex', 'sex.sex_id', '=', 'resident.sex_id')
            ->select([
                'resident.resident_id',
                'resident.last_name',
                'resident.first_name',
                'resident.middle_name',
                'resident.suffix',
                'resident.date_of_birth',
                'household.house_number',
                'street.street_name',
                'clan.clan_name',
                'sex.sex',
            ]);
        $this->applyResidentFilters($query);

        return $query;
    }

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
        $this->applyHouseholdFilters($query);

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

        $query = DB::table('household');
        $this->applyHouseholdFilters($query);

        return $query
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
        $this->applyResidentFilters($query);

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
        $this->constrainPets($query);

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

        $this->applyResidentFilters($query);
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

        $this->applyResidentFilters($query);
        $this->applyAudience($query, $audience);

        return $this->booleanGroups($query->get(), unanswered: false);
    }

    public function residentsByChildColumn(string $childTable, string $column, ?string $audience = null): array
    {
        $query = DB::table('resident')
            ->leftJoin($childTable, $childTable.'.resident_id', '=', 'resident.resident_id');

        $this->applyResidentFilters($query);
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
        $query = DB::table('pet_census')
            ->join('household', 'household.household_id', '=', 'pet_census.household_id')
            ->leftJoin('resident as head', 'head.resident_id', '=', 'household.head_resident_id')
            ->select('household.household_id as id', 'pet_census.'.$column.' as value')
            ->selectRaw($this->personNameSql('head').' as name')
            ->orderBy('household.household_id');
        $this->constrainPets($query);

        return $this->booleanGroups($query->get(), unanswered: false);
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

        $query = DB::table('pet_census');
        $this->constrainPets($query);

        return $query
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

        $this->applyResidentFilters($query);
        $this->applyAudience($query, $audience);

        return $this->booleanGroups($query->get(), unanswered: false);
    }

    public function residentsByCtcStatus(): array
    {
        $query = DB::table('resident')
            ->leftJoin('community_tax_cert as ctc', 'ctc.resident_id', '=', 'resident.resident_id');

        $this->applyResidentFilters($query);
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

        $query = DB::table('resident')
            ->leftJoin('sociocivic', 'sociocivic.resident_id', '=', 'resident.resident_id');
        $this->applyResidentFilters($query);

        return $query
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
        $query = DB::table('resident');
        $this->applyResidentFilters($query);
        $rows = $query
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

    private function populationFiltersActive(): bool
    {
        foreach ($this->populationFilters as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    private function attributeFiltersActive(): bool
    {
        return $this->attributeFilters !== [];
    }

    private function applyHouseholdFilters(QueryBuilder $query, string $alias = 'household'): void
    {
        if (! $this->populationFiltersActive() && ! $this->attributeFiltersActive()) {
            return;
        }

        if ($this->populationFilters['street'] !== null) {
            $query->where($alias.'.street_id', $this->populationFilters['street']);
        }

        if ($this->populationFilters['clan'] !== null) {
            $query->where($alias.'.clan_id', $this->populationFilters['clan']);
        }

        if ($this->populationFilters['age'] !== null || $this->populationFilters['sex'] !== null) {
            $query->whereExists(function (QueryBuilder $sub) use ($alias): void {
                $sub->from('resident as population_head')
                    ->whereColumn('population_head.resident_id', $alias.'.head_resident_id');
                $this->applyPersonAgeSex($sub, 'population_head');
            });
        }

        $this->applyAttributeFilters($query, $alias);
    }

    private function applyResidentFilters(QueryBuilder $query): void
    {
        if (! $this->populationFiltersActive()) {
            return;
        }

        if ($this->populationFilters['street'] !== null && ! $this->hasJoin($query, 'household')) {
            $query->leftJoin('household', 'household.household_id', '=', 'resident.household_id');
        }

        if ($this->populationFilters['street'] !== null) {
            $query->where('household.street_id', $this->populationFilters['street']);
        }

        if ($this->populationFilters['clan'] !== null) {
            $query->where('resident.clan_id', $this->populationFilters['clan']);
        }

        $this->applyPersonAgeSex($query, 'resident');
    }

    private function constrainPets(QueryBuilder $query): void
    {
        if (! $this->populationFiltersActive() && ! $this->attributeFiltersActive()) {
            return;
        }

        if (! $this->hasJoin($query, 'household')) {
            $query->join('household', 'household.household_id', '=', 'pet_census.household_id');
        }

        $this->applyHouseholdFilters($query);
    }

    private function applyPersonAgeSex(QueryBuilder $query, string $alias): void
    {
        $sex = $this->populationFilters['sex'];

        if ($sex === 'none') {
            $query->whereNull($alias.'.sex_id');
        } elseif ($sex !== null && $sex !== '') {
            $query->where($alias.'.sex_id', (int) $sex);
        }

        $age = $this->populationFilters['age'];

        if ($age === null || $age === '') {
            return;
        }

        if ($age === 'none') {
            $query->whereNull($alias.'.date_of_birth');

            return;
        }

        $group = collect(config('census_statistics.age_groups', []))->firstWhere('value', $age);

        if (! is_array($group)) {
            return;
        }

        $column = '`'.str_replace('`', '', $alias).'`.`date_of_birth`';
        $expression = 'timestampdiff(YEAR, '.$column.', curdate())';
        $query->whereRaw($expression.' >= ?', [(int) $group['min']]);

        if ($group['max'] !== null) {
            $query->whereRaw($expression.' <= ?', [(int) $group['max']]);
        }
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    private function applyAttributeFilters(QueryBuilder $query, string $alias): void
    {
        $petFilters = [];

        foreach ($this->attributeFilters as $filter) {
            if (str_starts_with((string) ($filter['kind'] ?? ''), 'pet_')) {
                $petFilters[] = $filter;

                continue;
            }

            match ($filter['kind'] ?? '') {
                'household_lookup' => $this->whereHouseholdLookup($query, $alias, $filter),
                'household_number' => $this->whereHouseholdNumber($query, $alias, $filter),
                'question' => $this->whereLatestQuestion($query, $alias, $filter),
                'question_bool' => $this->whereLatestQuestionBool($query, $alias, $filter),
                'presence' => $this->whereLatestPresence($query, $alias, $filter),
                'junction' => $this->whereLatestJunction($query, $alias, $filter),
                default => null,
            };
        }

        $this->applyPetAttributeFilters($query, $alias, $petFilters);
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    private function whereHouseholdLookup(QueryBuilder $query, string $alias, array $filter): void
    {
        $column = $alias.'.'.$filter['column'];

        if ($filter['value'] === 'none') {
            $query->whereNull($column);

            return;
        }

        $query->where($column, (int) $filter['value']);
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    private function whereHouseholdNumber(QueryBuilder $query, string $alias, array $filter): void
    {
        $column = $alias.'.'.$filter['column'];

        if ($filter['value'] === 'none') {
            $query->whereNull($column);

            return;
        }

        $number = (int) $filter['value'];

        if (($filter['null_as'] ?? null) === 0 && $number === 0) {
            $query->where(function (QueryBuilder $inner) use ($column): void {
                $inner->whereNull($column)->orWhere($column, 0);
            });

            return;
        }

        $query->where($column, $number);
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    private function whereLatestQuestion(QueryBuilder $query, string $alias, array $filter): void
    {
        $column = (string) $filter['column'];

        if ($filter['value'] === 'none') {
            $query->where(function (QueryBuilder $outer) use ($alias, $column): void {
                $outer->whereNotExists(function (QueryBuilder $sub) use ($alias): void {
                    $sub->from('household_questions as attr_hq')
                        ->whereColumn('attr_hq.household_id', $alias.'.household_id');
                })->orWhereExists(function (QueryBuilder $sub) use ($alias, $column): void {
                    $this->latestQuestion($sub, $alias)->whereNull('attr_hq.'.$column);
                });
            });

            return;
        }

        $query->whereExists(function (QueryBuilder $sub) use ($alias, $column, $filter): void {
            $this->latestQuestion($sub, $alias)->where('attr_hq.'.$column, (int) $filter['value']);
        });
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    private function whereLatestQuestionBool(QueryBuilder $query, string $alias, array $filter): void
    {
        $column = (string) $filter['column'];
        $value = (string) $filter['value'];

        if ($value === 'none') {
            $query->where(function (QueryBuilder $outer) use ($alias, $column): void {
                $outer->whereNotExists(function (QueryBuilder $sub) use ($alias): void {
                    $sub->from('household_questions as attr_hq')
                        ->whereColumn('attr_hq.household_id', $alias.'.household_id');
                })->orWhereExists(function (QueryBuilder $sub) use ($alias, $column): void {
                    $this->latestQuestion($sub, $alias)->whereNull('attr_hq.'.$column);
                });
            });

            return;
        }

        $query->whereExists(function (QueryBuilder $sub) use ($alias, $column, $value): void {
            $latest = $this->latestQuestion($sub, $alias);

            if ($value === 'yes') {
                $latest->where('attr_hq.'.$column, 1);

                return;
            }

            $latest->where('attr_hq.'.$column, 0);
        });
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    private function whereLatestPresence(QueryBuilder $query, string $alias, array $filter): void
    {
        $table = (string) $filter['table'];
        $owner = (string) $filter['owner'];

        if ($filter['value'] === 'yes') {
            $query->whereExists(function (QueryBuilder $sub) use ($alias, $table, $owner): void {
                $this->latestQuestion($sub, $alias)
                    ->join($table.' as attr_presence', 'attr_presence.'.$owner, '=', 'attr_hq.household_questions_id');
            });

            return;
        }

        $query->whereExists(function (QueryBuilder $sub) use ($alias, $table, $owner): void {
            $this->latestQuestion($sub, $alias)
                ->whereNotExists(function (QueryBuilder $missing) use ($table, $owner): void {
                    $missing->from($table.' as attr_presence')
                        ->whereColumn('attr_presence.'.$owner, 'attr_hq.household_questions_id');
                });
        });
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    private function whereLatestJunction(QueryBuilder $query, string $alias, array $filter): void
    {
        $table = (string) $filter['table'];
        $column = (string) $filter['column'];
        $query->whereExists(function (QueryBuilder $sub) use ($alias, $table, $column, $filter): void {
            $this->latestQuestion($sub, $alias)
                ->join($table.' as attr_j', 'attr_j.household_question_id', '=', 'attr_hq.household_questions_id')
                ->where('attr_j.'.$column, (int) $filter['value']);
        });
    }

    private function latestQuestion(QueryBuilder $sub, string $alias): QueryBuilder
    {
        return $sub->from('household_questions as attr_hq')
            ->whereColumn('attr_hq.household_id', $alias.'.household_id')
            ->whereRaw('attr_hq.household_questions_id = (select max(inner_hq.household_questions_id) from household_questions as inner_hq where inner_hq.household_id = attr_hq.household_id)');
    }

    /**
     * @param  list<array<string, mixed>>  $filters
     */
    private function applyPetAttributeFilters(QueryBuilder $query, string $alias, array $filters): void
    {
        if ($filters === []) {
            return;
        }

        if ($this->queriesPetCensus($query)) {
            foreach ($filters as $filter) {
                $this->constrainPetRow($query, 'pet_census', $filter);
            }

            return;
        }

        $query->whereExists(function (QueryBuilder $sub) use ($alias, $filters): void {
            $sub->from('pet_census as attr_pet')
                ->whereColumn('attr_pet.household_id', $alias.'.household_id');

            foreach ($filters as $filter) {
                $this->constrainPetRow($sub, 'attr_pet', $filter);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    private function constrainPetRow(QueryBuilder $query, string $alias, array $filter): void
    {
        $kind = (string) ($filter['kind'] ?? '');

        if ($kind === 'pet_lookup') {
            $column = $alias.'.'.$filter['column'];
            $filter['value'] === 'none'
                ? $query->whereNull($column)
                : $query->where($column, (int) $filter['value']);

            return;
        }

        if ($kind === 'pet_bool') {
            $column = $alias.'.'.$filter['column'];

            if ($filter['value'] === 'yes') {
                $query->where($column, 1);

                return;
            }

            $query->where(function (QueryBuilder $inner) use ($column): void {
                $inner->where($column, 0)->orWhereNull($column);
            });

            return;
        }

        if ($kind !== 'pet_rabies') {
            return;
        }

        $date = $alias.'.rabies_vaccination_date';
        $cutoff = now()->subMonths(12)->toDateString();

        match ((string) $filter['value']) {
            'within_12' => $query->whereNotNull($date)->whereDate($date, '>=', $cutoff),
            'older_12' => $query->whereNotNull($date)->whereDate($date, '<', $cutoff),
            default => $query->whereNull($date),
        };
    }

    private function queriesPetCensus(QueryBuilder $query): bool
    {
        return $query->from === 'pet_census' || $this->hasJoin($query, 'pet_census');
    }

    private function hasJoin(QueryBuilder $query, string $table): bool
    {
        foreach ($query->joins ?? [] as $join) {
            $name = $join->table;

            if ($name instanceof \Illuminate\Contracts\Database\Query\Expression) {
                $name = $name->getValue(DB::connection()->getQueryGrammar());
            }

            if (preg_match('/(?:^|\s)'.preg_quote($table, '/').'(?:\s|$)/', (string) $name) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function optionRows(string $table, string $idColumn, string $labelColumn): array
    {
        return DB::table($table)
            ->orderBy($labelColumn)
            ->get([$idColumn.' as id', $labelColumn.' as label'])
            ->map(fn (object $row): array => [
                'value' => (string) $row->id,
                'label' => (string) $row->label,
            ])
            ->all();
    }

    /**
     * Every household with its latest household_questions row as `hq`, the
     * same row Report Generation reads. Unanswered households keep a null row.
     */
    private function householdsWithLatestQuestions(): QueryBuilder
    {
        $query = DB::table('household')
            ->leftJoinSub($this->latestQuestionIds(), 'latest', 'latest.household_id', '=', 'household.household_id')
            ->leftJoin('household_questions as hq', 'hq.household_questions_id', '=', 'latest.latest_id');
        $this->applyHouseholdFilters($query);

        return $query;
    }

    private function latestQuestionIds(): QueryBuilder
    {
        $query = DB::table('household_questions')
            ->selectRaw('household_questions.household_id as household_id, max(household_questions.household_questions_id) as latest_id')
            ->groupBy('household_questions.household_id');

        if ($this->populationFiltersActive() || $this->attributeFiltersActive()) {
            $query->join('household', 'household.household_id', '=', 'household_questions.household_id');
            $this->applyHouseholdFilters($query);
        }

        return $query;
    }

    /**
     * @param  array{table: string, from: string, to: string, owner: string}|null  $via
     */
    private function residentsWithChild(string $childTable, ?array $via): QueryBuilder
    {
        $query = DB::table('resident');

        if ($via === null) {
            $query->leftJoin($childTable, $childTable.'.resident_id', '=', 'resident.resident_id');
        } else {
            $query
                ->leftJoin($via['table'], $via['table'].'.'.$via['owner'], '=', 'resident.resident_id')
                ->leftJoin($childTable, $childTable.'.'.$via['from'], '=', $via['table'].'.'.$via['to']);
        }

        $this->applyResidentFilters($query);

        return $query;
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
