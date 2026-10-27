<?php

namespace App\Services;

use App\Models\Logs\Action;
use App\Models\ResidentManagement\Sociocivic\SoloParentStatus;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Support\CensusStatisticCatalog;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CensusDashboardService
{
    public function __construct(
        protected AuditLogRepositoryInterface $auditLogRepository,
    ) {}

    /**
     * @return array{tiles: list<array<string, mixed>>}
     */
    public function tiles(User $user): array
    {
        $tiles = [];

        foreach (CensusStatisticCatalog::visible($this->hidesSensitive($user)) as $id => $statistic) {
            $tiles[] = [
                'id' => $id,
                'label' => $statistic['label'],
                'icon' => $statistic['icon'],
                'count' => $this->count($id, $statistic),
                'route' => '/dashboard/'.$id,
            ];
        }

        return [
            'tiles' => $tiles,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function show(User $user, string $id, array $query): array
    {
        $statistic = $this->authorize($user, $id);
        $filters = $this->filters($query);
        $scoped = $this->scoped($statistic);
        $total = (clone $scoped)->count();
        $filteredQuery = $this->applyFilters($scoped, $statistic, $filters);
        $filtered = (clone $filteredQuery)->count();

        return [
            'statistic' => [
                'id' => $id,
                'label' => $statistic['label'],
                'table_title' => $statistic['table_title'],
                'empty' => $statistic['empty'],
                'chart_title' => $statistic['label'],
            ],
            'can_export' => $this->canExport($user),
            'total' => $total,
            'filtered' => $filtered,
            'filters' => $this->filterDefinitions($statistic, $filters),
            'active' => $this->activeFilters($statistic, $filters),
            'chart' => $this->chart($filteredQuery, $statistic),
            'columns' => $this->columns($statistic),
            'rows' => $this->pageRows($filteredQuery, $statistic, $filters),
            'pagination' => [
                'page' => $filters['page'],
                'per_page' => $this->perPage(),
                'last_page' => max(1, (int) ceil($filtered / $this->perPage())),
                'total' => $filtered,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function export(User $user, string $id, array $query): Response
    {
        if (! $this->canExport($user)) {
            throw new AccessDeniedHttpException('You cannot export census statistics.');
        }

        $statistic = $this->authorize($user, $id);
        $filters = $this->filters($query);
        $filters['page'] = 1;
        $filteredQuery = $this->applyFilters($this->scoped($statistic), $statistic, $filters);
        $columns = $this->columns($statistic);
        $rows = $this->mapRows((clone $filteredQuery)->orderBy($this->sortColumn($statistic, $filters), $filters['dir'])->get(), $statistic);

        $lines = [$this->csvLine(array_column($columns, 'label'))];

        foreach ($rows as $row) {
            $lines[] = $this->csvLine(array_map(
                fn (array $column): string => (string) ($row[$column['key']] ?? ''),
                $columns,
            ));
        }

        $this->auditLogRepository->log(
            performedByUserId: $user->user_id,
            actionId: Action::EXPORT,
            recordId: 0,
            description: 'Exported '.$statistic['label'].' statistic',
            oldValue: $this->filterSummary($filters),
            newValue: now()->toDateTimeString(),
            target: $id,
            entity: 'census_statistic',
        );

        $filename = str_replace(' ', '-', strtolower($statistic['label']));

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
        ]);
    }

    public function hidesSensitive(User $user): bool
    {
        return $user->isEncoder() && ! $user->isSuperAdmin();
    }

    public function canExport(User $user): bool
    {
        if ($this->hidesSensitive($user)) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermission('report.create');
    }

    /**
     * @param  array<string, mixed>  $statistic
     */
    private function count(string $id, array $statistic): int
    {
        return $this->scoped($statistic)->count();
    }

    /**
     * @return array<string, mixed>
     */
    private function authorize(User $user, string $id): array
    {
        try {
            $statistic = CensusStatisticCatalog::find($id);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException('That statistic does not exist.');
        }

        if (! empty($statistic['sensitive']) && $this->hidesSensitive($user)) {
            throw new AccessDeniedHttpException('You cannot view this statistic.');
        }

        return $statistic;
    }

    /**
     * @param  array<string, mixed>  $statistic
     */
    private function scoped(array $statistic): Builder
    {
        return match ($statistic['source']) {
            'residents' => $this->residents($statistic['scope']),
            'households' => $this->households(),
            'clans' => $this->clans(),
            'streets' => $this->streets(),
            default => throw new InvalidArgumentException('Unknown statistic source.'),
        };
    }

    private function residents(string $scope): Builder
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
                'resident.sex_id',
                'resident.clan_id',
                'household.house_number',
                'household.street_id',
                'street.street_name',
                'clan.clan_name',
                'sex.sex',
            ]);

        $age = 'timestampdiff(YEAR, resident.date_of_birth, curdate())';

        match ($scope) {
            'all' => null,
            'seniors' => $query->whereRaw($age.' >= 60'),
            'youth' => $query->whereRaw($age.' between 15 and 30'),
            'voters' => $query->whereExists(function (Builder $sub): void {
                $sub->from('sociocivic')
                    ->whereColumn('sociocivic.resident_id', 'resident.resident_id')
                    ->whereNotNull('sociocivic.registered_barangay_voter')
                    ->whereRaw("trim(sociocivic.registered_barangay_voter) <> ''")
                    ->whereRaw("lower(trim(sociocivic.registered_barangay_voter)) not in ('yes', 'no')");
            }),
            'solo_parents' => $query->whereExists(function (Builder $sub): void {
                $sub->from('sociocivic')
                    ->whereColumn('sociocivic.resident_id', 'resident.resident_id')
                    ->where('sociocivic.solo_parent_status_id', SoloParentStatus::REGISTERED_SOLO_PARENT);
            }),
            'pwd' => $query->whereExists(function (Builder $sub): void {
                $sub->from('health')
                    ->join('disability', 'disability.disability_id', '=', 'health.disability_id')
                    ->whereColumn('health.resident_id', 'resident.resident_id')
                    ->whereRaw("lower(disability.disability) not in ('none', 'n/a', 'na', 'n.a', 'n.a.', 'not applicable')");
            }),
            default => throw new InvalidArgumentException("Unknown resident scope [{$scope}]."),
        };

        return $query;
    }

    private function households(): Builder
    {
        return DB::table('household')
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
    }

    private function clans(): Builder
    {
        return DB::table('clan')
            ->select(['clan.clan_id', 'clan.clan_name'])
            ->selectSub(
                DB::table('household')
                    ->selectRaw('count(*)')
                    ->whereColumn('household.clan_id', 'clan.clan_id'),
                'household_count',
            )
            ->selectSub(
                DB::table('resident')
                    ->selectRaw('count(*)')
                    ->whereColumn('resident.clan_id', 'clan.clan_id'),
                'resident_count',
            );
    }

    private function streets(): Builder
    {
        return DB::table('street')
            ->select(['street.street_id', 'street.street_name'])
            ->selectSub(
                DB::table('household')
                    ->selectRaw('count(*)')
                    ->whereColumn('household.street_id', 'street.street_id'),
                'household_count',
            )
            ->selectSub(
                DB::table('resident')
                    ->join('household', 'household.household_id', '=', 'resident.household_id')
                    ->selectRaw('count(*)')
                    ->whereColumn('household.street_id', 'street.street_id'),
                'resident_count',
            );
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{street: ?int, clan: ?int, age: ?string, sex: ?string, q: string, sort: string, dir: string, page: int}
     */
    private function filters(array $query): array
    {
        $age = isset($query['age']) ? (string) $query['age'] : '';
        $allowedAges = array_column(config('census_statistics.age_groups', []), 'value');

        return [
            'street' => $this->nullableInt($query['street'] ?? null),
            'clan' => $this->nullableInt($query['clan'] ?? null),
            'age' => in_array($age, $allowedAges, true) || $age === 'none' ? $age : null,
            'sex' => $this->sexFilter($query['sex'] ?? null),
            'q' => trim((string) ($query['q'] ?? '')),
            'sort' => (string) ($query['sort'] ?? 'name'),
            'dir' => strtolower((string) ($query['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc',
            'page' => max(1, (int) ($query['page'] ?? 1)),
        ];
    }

    /**
     * @param  array<string, mixed>  $statistic
     * @param  array{street: ?int, clan: ?int, age: ?string, sex: ?string, q: string, sort: string, dir: string, page: int}  $filters
     */
    private function applyFilters(Builder $query, array $statistic, array $filters): Builder
    {
        $allowed = $statistic['filters'];

        if (in_array('street', $allowed, true) && $filters['street'] !== null) {
            $this->whereStreet($query, $statistic['source'], $filters['street']);
        }

        if (in_array('clan', $allowed, true) && $filters['clan'] !== null) {
            $this->whereClan($query, $statistic['source'], $filters['clan']);
        }

        if (in_array('age', $allowed, true) && $filters['age'] !== null && $statistic['source'] === 'residents') {
            $this->whereAge($query, $filters['age']);
        }

        if (in_array('sex', $allowed, true) && $filters['sex'] !== null && $statistic['source'] === 'residents') {
            $filters['sex'] === 'none'
                ? $query->whereNull('resident.sex_id')
                : $query->where('resident.sex_id', (int) $filters['sex']);
        }

        if ($filters['q'] !== '') {
            $this->whereSearch($query, $statistic['source'], $filters['q']);
        }

        return $query;
    }

    private function whereStreet(Builder $query, string $source, int $streetId): void
    {
        match ($source) {
            'residents' => $query->where('household.street_id', $streetId),
            'households' => $query->where('household.street_id', $streetId),
            'clans' => $query->whereExists(function (Builder $sub) use ($streetId): void {
                $sub->from('household')
                    ->whereColumn('household.clan_id', 'clan.clan_id')
                    ->where('household.street_id', $streetId);
            }),
            'streets' => $query->where('street.street_id', $streetId),
            default => null,
        };
    }

    private function whereClan(Builder $query, string $source, int $clanId): void
    {
        match ($source) {
            'residents' => $query->where('resident.clan_id', $clanId),
            'households' => $query->where('household.clan_id', $clanId),
            'clans' => $query->where('clan.clan_id', $clanId),
            'streets' => $query->whereExists(function (Builder $sub) use ($clanId): void {
                $sub->from('household')
                    ->whereColumn('household.street_id', 'street.street_id')
                    ->where('household.clan_id', $clanId);
            }),
            default => null,
        };
    }

    private function whereAge(Builder $query, string $age): void
    {
        if ($age === 'none') {
            $query->whereNull('resident.date_of_birth');

            return;
        }

        $group = collect(config('census_statistics.age_groups', []))->firstWhere('value', $age);

        if (! is_array($group)) {
            return;
        }

        $expression = 'timestampdiff(YEAR, resident.date_of_birth, curdate())';
        $query->whereRaw($expression.' >= ?', [(int) $group['min']]);

        if ($group['max'] !== null) {
            $query->whereRaw($expression.' <= ?', [(int) $group['max']]);
        }
    }

    private function whereSearch(Builder $query, string $source, string $term): void
    {
        $like = '%'.addcslashes(mb_strtolower($term), '%_\\').'%';

        match ($source) {
            'residents' => $query->where(function (Builder $inner) use ($like, $term): void {
                $inner->whereRaw(
                    "lower(concat(resident.last_name, ', ', resident.first_name, ' ', coalesce(resident.middle_name, ''))) like ? escape '\\\\'",
                    [$like],
                );

                if (ctype_digit($term)) {
                    $inner->orWhere('resident.resident_id', (int) $term);
                }
            }),
            'households' => $query->whereRaw(
                "lower(concat(coalesce(head.last_name, ''), ', ', coalesce(head.first_name, ''))) like ? escape '\\\\'",
                [$like],
            ),
            'clans' => $query->whereRaw("lower(clan.clan_name) like ? escape '\\\\'", [$like]),
            'streets' => $query->whereRaw("lower(street.street_name) like ? escape '\\\\'", [$like]),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $statistic
     * @param  array{street: ?int, clan: ?int, age: ?string, sex: ?string, q: string, sort: string, dir: string, page: int}  $filters
     * @return list<array<string, mixed>>
     */
    private function filterDefinitions(array $statistic, array $filters): array
    {
        $definitions = [];

        foreach ($statistic['filters'] as $key) {
            $definitions[] = match ($key) {
                'street' => $this->lookupFilter('street', 'Street', 'street', 'street_id', 'street_name', $filters['street']),
                'clan' => $this->lookupFilter('clan', 'Clan', 'clan', 'clan_id', 'clan_name', $filters['clan']),
                'sex' => $this->lookupFilter('sex', 'Sex', 'sex', 'sex_id', 'sex', $filters['sex'] !== null && $filters['sex'] !== 'none' ? (int) $filters['sex'] : null),
                'age' => [
                    'key' => 'age',
                    'label' => 'Age group',
                    'value' => $filters['age'],
                    'options' => array_map(
                        fn (array $group): array => ['value' => $group['value'], 'label' => $group['label']],
                        config('census_statistics.age_groups', []),
                    ),
                ],
                default => null,
            };
        }

        return array_values(array_filter($definitions));
    }

    /**
     * @return array{key: string, label: string, value: ?string, options: list<array{value: string, label: string}>}
     */
    private function lookupFilter(string $key, string $label, string $table, string $idColumn, string $labelColumn, ?int $selected): array
    {
        $options = DB::table($table)
            ->orderBy($labelColumn)
            ->get([$idColumn.' as id', $labelColumn.' as label'])
            ->map(fn (object $row): array => [
                'value' => (string) $row->id,
                'label' => (string) $row->label,
            ])
            ->all();

        return [
            'key' => $key,
            'label' => $label,
            'value' => $selected === null ? null : (string) $selected,
            'options' => $options,
        ];
    }

    /**
     * @param  array<string, mixed>  $statistic
     * @param  array{street: ?int, clan: ?int, age: ?string, sex: ?string, q: string, sort: string, dir: string, page: int}  $filters
     * @return list<array{key: string, label: string, value: string}>
     */
    private function activeFilters(array $statistic, array $filters): array
    {
        $active = [];

        foreach ($this->filterDefinitions($statistic, $filters) as $filter) {
            if ($filter['value'] === null || $filter['value'] === '') {
                continue;
            }

            $match = collect($filter['options'])->firstWhere('value', $filter['value']);
            $active[] = [
                'key' => $filter['key'],
                'label' => $filter['label'],
                'value' => $match['label'] ?? $filter['value'],
            ];
        }

        if ($filters['q'] !== '') {
            $active[] = ['key' => 'q', 'label' => 'Search', 'value' => $filters['q']];
        }

        return $active;
    }

    /**
     * @param  array<string, mixed>  $statistic
     * @return array{title: string, slices: list<array<string, mixed>>}
     */
    private function chart(Builder $query, array $statistic): array
    {
        $rows = match ($statistic['chart']) {
            'sex' => $this->groupLookup($query, 'sex.sex_id', 'sex.sex'),
            'street' => $statistic['source'] === 'streets'
                ? $this->entitySlices($query, 'street', 'street_id', 'street_name')
                : $this->groupLookup($query, $statistic['source'] === 'households' ? 'household.street_id' : 'street.street_id', 'street.street_name'),
            'clan' => $statistic['source'] === 'clans'
                ? $this->entitySlices($query, 'clan', 'clan_id', 'clan_name')
                : $this->groupLookup($query, $statistic['source'] === 'households' ? 'household.clan_id' : 'clan.clan_id', 'clan.clan_name'),
            'age' => $this->groupAge($query),
            default => [],
        };

        $sum = array_sum(array_map(fn (array $row): int => (int) $row['count'], $rows));
        $tenths = [];
        $remainders = [];

        foreach (array_values($rows) as $index => $row) {
            $count = (int) $row['count'];
            $tenths[$index] = $sum > 0 ? intdiv($count * 1000, $sum) : 0;
            $remainders[$index] = $sum > 0 ? ($count * 1000) % $sum : 0;
        }

        $leftover = $sum > 0 ? 1000 - array_sum($tenths) : 0;
        arsort($remainders);

        foreach (array_keys($remainders) as $index) {
            if ($leftover < 1) {
                break;
            }

            $tenths[$index]++;
            $leftover--;
        }

        $colors = config('census_statistics.colors', []);
        $slices = [];

        foreach (array_values($rows) as $index => $row) {
            $count = (int) $row['count'];
            $slices[] = [
                'id' => $row['id'],
                'label' => $row['label'],
                'count' => $count,
                'percent' => ($tenths[$index] ?? 0) / 10,
                'color' => $colors[$index % max(1, count($colors))],
                'filter' => $this->chartFilterKey($statistic),
            ];
        }

        return [
            'title' => $statistic['label'],
            'slices' => $slices,
        ];
    }

    /**
     * @return list<array{id: ?string, label: string, count: int}>
     */
    private function groupLookup(Builder $query, string $idColumn, string $labelColumn): array
    {
        return (clone $query)
            ->select(DB::raw($idColumn.' as id, '.$labelColumn.' as label, count(*) as total'))
            ->groupByRaw($idColumn.', '.$labelColumn)
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $row): array => [
                'id' => $row->id === null ? 'none' : (string) $row->id,
                'label' => $row->label !== null && trim((string) $row->label) !== '' ? (string) $row->label : 'Not specified',
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{id: ?string, label: string, count: int}>
     */
    private function entitySlices(Builder $query, string $table, string $idColumn, string $labelColumn): array
    {
        $countSql = $table === 'clan'
            ? '(select count(*) from resident where resident.clan_id = clan.clan_id)'
            : '(select count(*) from resident inner join household on household.household_id = resident.household_id where household.street_id = street.street_id)';

        return (clone $query)
            ->select(DB::raw($table.'.'.$idColumn.' as id, '.$table.'.'.$labelColumn.' as label, '.$countSql.' as total'))
            ->orderByDesc('total')
            ->get()
            ->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'label' => (string) $row->label,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{id: ?string, label: string, count: int}>
     */
    private function groupAge(Builder $query): array
    {
        $age = 'timestampdiff(YEAR, resident.date_of_birth, curdate())';
        $case = "case when resident.date_of_birth is null then 'none'";

        foreach (config('census_statistics.age_groups', []) as $group) {
            $max = $group['max'] === null ? '' : ' and '.$age.' <= '.(int) $group['max'];
            $case .= ' when '.$age.' >= '.(int) $group['min'].$max." then '".$group['value']."'";
        }

        $case .= " else 'none' end";
        $labels = collect(config('census_statistics.age_groups', []))->pluck('label', 'value');

        return (clone $query)
            ->select(DB::raw($case.' as id, count(*) as total'))
            ->groupByRaw($case)
            ->get()
            ->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'label' => $row->id === 'none' ? 'Not specified' : (string) ($labels[$row->id] ?? $row->id),
                'count' => (int) $row->total,
            ])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $statistic
     */
    private function chartFilterKey(array $statistic): ?string
    {
        $key = match ($statistic['chart']) {
            'sex' => 'sex',
            'street' => 'street',
            'clan' => 'clan',
            'age' => 'age',
            default => null,
        };

        return in_array($key, $statistic['filters'], true) ? $key : null;
    }

    /**
     * @param  array<string, mixed>  $statistic
     * @return list<array{key: string, label: string}>
     */
    private function columns(array $statistic): array
    {
        return array_map(
            fn (array $column): array => ['key' => $column['key'], 'label' => $column['label']],
            $statistic['columns'],
        );
    }

    /**
     * @param  array<string, mixed>  $statistic
     * @param  array{street: ?int, clan: ?int, age: ?string, sex: ?string, q: string, sort: string, dir: string, page: int}  $filters
     * @return list<array<string, string>>
     */
    private function pageRows(Builder $query, array $statistic, array $filters): array
    {
        $perPage = $this->perPage();
        $rows = (clone $query)
            ->orderBy($this->sortColumn($statistic, $filters), $filters['dir'])
            ->forPage($filters['page'], $perPage)
            ->get();

        return $this->mapRows($rows, $statistic);
    }

    /**
     * @param  iterable<int, object>  $rows
     * @param  array<string, mixed>  $statistic
     * @return list<array<string, string>>
     */
    private function mapRows(iterable $rows, array $statistic): array
    {
        $mapped = [];

        foreach ($rows as $row) {
            $record = match ($statistic['source']) {
                'residents' => $this->residentRecord($row),
                'households' => $this->householdRecord($row),
                'clans' => $this->clanRecord($row),
                'streets' => $this->streetRecord($row),
                default => [],
            };
            $cells = [];

            foreach ($statistic['columns'] as $column) {
                $cells[$column['key']] = $record[$column['key']] ?? '—';
            }

            $mapped[] = $cells;
        }

        return $mapped;
    }

    /**
     * @return array<string, string>
     */
    private function residentRecord(object $row): array
    {
        return [
            'record_number' => 'RBIM-'.str_pad((string) $row->resident_id, 4, '0', STR_PAD_LEFT),
            'name' => $this->personName($row->last_name, $row->first_name, $row->middle_name ?? null, $row->suffix ?? null),
            'date_of_birth' => $row->date_of_birth ? date('M j, Y', strtotime((string) $row->date_of_birth)) : '—',
            'address' => $this->address($row->house_number ?? null, $row->street_name ?? null),
            'sex' => $row->sex ?: '—',
            'clan' => $row->clan_name ?: '—',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function householdRecord(object $row): array
    {
        return [
            'record_number' => 'HH-'.str_pad((string) $row->household_id, 4, '0', STR_PAD_LEFT),
            'name' => $this->personName($row->last_name ?? null, $row->first_name ?? null, $row->middle_name ?? null, $row->suffix ?? null),
            'address' => $this->address($row->house_number ?? null, $row->street_name ?? null),
            'clan' => $row->clan_name ?: '—',
            'members' => (string) ($row->member_count ?? 0),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function clanRecord(object $row): array
    {
        return [
            'name' => (string) $row->clan_name,
            'households' => (string) ($row->household_count ?? 0),
            'residents' => (string) ($row->resident_count ?? 0),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function streetRecord(object $row): array
    {
        return [
            'name' => (string) $row->street_name,
            'households' => (string) ($row->household_count ?? 0),
            'residents' => (string) ($row->resident_count ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $statistic
     * @param  array{street: ?int, clan: ?int, age: ?string, sex: ?string, q: string, sort: string, dir: string, page: int}  $filters
     */
    private function sortColumn(array $statistic, array $filters): string
    {
        $sort = $filters['sort'];
        $source = $statistic['source'];

        $columns = match ($source) {
            'residents' => [
                'record_number' => 'resident.resident_id',
                'name' => 'resident.last_name',
                'date_of_birth' => 'resident.date_of_birth',
                'address' => 'street.street_name',
                'sex' => 'sex.sex',
                'clan' => 'clan.clan_name',
            ],
            'households' => [
                'record_number' => 'household.household_id',
                'name' => 'head.last_name',
                'address' => 'street.street_name',
                'clan' => 'clan.clan_name',
                'members' => 'member_count',
            ],
            'clans' => [
                'name' => 'clan.clan_name',
                'households' => 'household_count',
                'residents' => 'resident_count',
            ],
            'streets' => [
                'name' => 'street.street_name',
                'households' => 'household_count',
                'residents' => 'resident_count',
            ],
            default => ['name' => 'resident.resident_id'],
        };

        return $columns[$sort] ?? reset($columns);
    }

    private function personName(?string $last, ?string $first, ?string $middle, ?string $suffix): string
    {
        $given = trim(implode(' ', array_filter([$first, $middle, $suffix], fn (?string $part): bool => trim((string) $part) !== '')));
        $family = trim((string) $last);

        if ($family !== '' && $given !== '') {
            return $family.', '.$given;
        }

        return $family !== '' ? $family : ($given !== '' ? $given : '—');
    }

    private function address(mixed $houseNumber, mixed $street): string
    {
        $parts = array_filter([
            $houseNumber !== null && $houseNumber !== '' ? (string) $houseNumber : null,
            $street !== null && trim((string) $street) !== '' ? (string) $street : null,
        ]);

        return $parts === [] ? '—' : implode(' ', $parts);
    }

    /**
     * @param  array{street: ?int, clan: ?int, age: ?string, sex: ?string, q: string, sort: string, dir: string, page: int}  $filters
     */
    private function filterSummary(array $filters): string
    {
        $parts = [];

        foreach (['street', 'clan', 'age', 'sex'] as $key) {
            if ($filters[$key] !== null && $filters[$key] !== '') {
                $parts[] = $key.'='.$filters[$key];
            }
        }

        if ($filters['q'] !== '') {
            $parts[] = 'q='.$filters['q'];
        }

        $summary = $parts === [] ? 'none' : implode(';', $parts);

        return mb_substr($summary, 0, 45);
    }

    /**
     * @param  list<string>  $values
     */
    private function csvLine(array $values): string
    {
        return implode(',', array_map(function (string $value): string {
            return '"'.str_replace('"', '""', $value).'"';
        }, $values));
    }

    private function perPage(): int
    {
        return max(1, (int) config('census_statistics.per_page', 10));
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! ctype_digit((string) $value)) {
            return null;
        }

        return (int) $value;
    }

    private function sexFilter(mixed $value): ?string
    {
        $text = trim((string) $value);

        if ($text === '' ) {
            return null;
        }

        if ($text === 'none' || ctype_digit($text)) {
            return $text;
        }

        return null;
    }
}
