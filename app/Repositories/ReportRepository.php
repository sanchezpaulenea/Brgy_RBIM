<?php

namespace App\Repositories;

use App\Models\ResidentManagement\Demographic\Sex;
use App\Repositories\Interfaces\ReportRepositoryInterface;
use App\Services\ReportSchema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ReportRepository implements ReportRepositoryInterface
{
    /**
     * @return list<array{id: int, label: string}>
     */
    public function getCategoryOptions(string $categoryKey, string $filterKey, ?string $search = null, bool $all = false, ?string $mode = null): array
    {
        $filter = ReportSchema::filterDefinition(ReportSchema::category($categoryKey), $filterKey);

        if (($filter['type'] ?? '') !== 'lookup') {
            return [];
        }

        $modelClass = $filter['model'];
        $prototype = new $modelClass;
        $labelColumn = (string) $filter['label_column'];
        $idColumn = (string) $filter['id_column'];
        $term = trim((string) $search);
        $omit = is_array($filter['omit_sentinel_from'] ?? null) ? $filter['omit_sentinel_from'] : [];
        $sentinelId = is_string($mode) && in_array($mode, $omit, true)
            ? ReportSchema::sentinelId($filter)
            : null;

        $options = $modelClass::query()
            ->when($term !== '', function (Builder $query) use ($labelColumn, $term): void {
                $query->where($labelColumn, 'like', $this->likeContains($term));
            })
            ->when($sentinelId !== null, fn (Builder $query) => $query->where($idColumn, '!=', $sentinelId))
            ->orderBy($labelColumn)
            ->when(! $all, fn (Builder $query) => $query->limit(ReportSchema::OPTION_LIMIT))
            ->get();

        return $options
            ->map(fn (Model $option): array => $this->optionPayload($option, $prototype, $labelColumn, (string) $filter['id_column']))
            ->all();
    }

    /**
     * @param  list<int>  $ids
     * @return list<array{id: int, label: string}>
     */
    public function filterLabels(string $categoryKey, string $filterKey, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $filter = ReportSchema::filterDefinition(ReportSchema::category($categoryKey), $filterKey);
        $modelClass = $filter['model'];
        $prototype = new $modelClass;
        $keyName = (string) $filter['id_column'];
        $labelColumn = (string) $filter['label_column'];

        $labels = $modelClass::query()
            ->whereIn($keyName, $ids)
            ->get()
            ->keyBy(fn (Model $option): int => (int) $option->getAttribute($keyName));

        $ordered = [];

        foreach ($ids as $id) {
            $option = $labels->get((int) $id);

            if ($option instanceof Model) {
                $ordered[] = $this->optionPayload($option, $prototype, $labelColumn, $keyName);
            }
        }

        return $ordered;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $selectedColumns
     */
    public function buildReportQuery(string $categoryKey, array $filters, array $selectedColumns): Builder
    {
        $category = ReportSchema::category($categoryKey);
        $modelClass = ReportSchema::levelModel($category['level']);
        $prototype = new $modelClass;
        $table = $prototype->getTable();
        $presented = ReportSchema::presentColumns($categoryKey, $selectedColumns, $filters);
        $presentedKeys = array_flip(array_column($presented, 'key'));

        $query = $modelClass::query()->select($table.'.*');
        $petConstraints = [];
        $relationConstraints = [];

        foreach ($category['filters'] ?? [] as $filter) {
            if (! is_array($filter) || ! ReportSchema::filterApplies($category, $filter, $filters)) {
                continue;
            }

            $state = ReportSchema::filterState($filters, (string) $filter['key']);
            $this->collectFilter($query, $filter, $state, $petConstraints, $relationConstraints);
        }

        if (($category['questions'] ?? false) === true && ! ReportSchema::includesUnanswered($category, $filters)) {
            $this->ensureQuestionsJoin($query);
            $query->whereNotNull('hq.household_questions_id');
        }

        if ($petConstraints !== [] && $this->petsAreRestricted($category, $filters)) {
            $query->whereExists(function (QueryBuilder $sub) use ($petConstraints): void {
                $sub->selectRaw('1')
                    ->from('pet_census')
                    ->whereColumn('pet_census.household_id', 'household.household_id');

                foreach ($petConstraints as $apply) {
                    $apply($sub);
                }
            });
        }

        $this->applyAudience($query, $category);
        $this->applySelectedRelations($query, $presented, $petConstraints, $relationConstraints);
        $this->applyCounts($query, $category, $filters, $presentedKeys, $petConstraints);
        $this->applySorts($query, $categoryKey, $filters, $selectedColumns, $petConstraints);
        $query->orderBy($table.'.'.$prototype->getKeyName());

        return $query;
    }

    public function unansweredHouseholdCount(): int
    {
        return (int) DB::table('household')
            ->leftJoin('household_questions as hq', 'hq.household_id', '=', 'household.household_id')
            ->whereNull('hq.household_questions_id')
            ->count('household.household_id');
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     * @param  list<callable(Builder|QueryBuilder): void>  $petConstraints
     * @param  array<string, callable(Builder): void>  $relationConstraints
     */
    private function collectFilter(Builder $query, array $filter, array $state, array &$petConstraints, array &$relationConstraints): void
    {
        $apply = (string) ($filter['apply'] ?? '');

        if (($filter['type'] ?? '') === 'checks') {
            return;
        }

        if (($filter['type'] ?? '') === 'lookup') {
            $this->rememberRelationConstraint($filter, $state, $relationConstraints);

            if (ReportSchema::isAll($filter, $state)) {
                return;
            }

            $ids = ReportSchema::selectedLookupIds($filter, $state);

            if ($ids === []) {
                $query->whereRaw('0 = 1');

                return;
            }

            if ($apply === 'pet') {
                $column = $this->assertIdent((string) $filter['column']);
                $petConstraints[] = function ($petQuery) use ($column, $ids): void {
                    $petQuery->whereIn('pet_census.'.$column, $ids);
                };

                return;
            }

            if ($apply === 'junction') {
                $this->ensureQuestionsJoin($query);
                $this->whereJunction($query, $filter, $ids);

                return;
            }

            if ($apply === 'subrecord') {
                $this->whereChildRows($query, $filter, function (QueryBuilder $sub) use ($filter, $ids): void {
                    $table = $this->assertIdent((string) $filter['subrecord_table']);
                    $column = $this->assertIdent((string) $filter['column']);
                    $sub->whereIn($table.'.'.$column, $ids);
                });

                return;
            }

            $this->whereLookupColumn($query, $filter, $ids);

            return;
        }

        if (($filter['type'] ?? '') === 'range') {
            if (ReportSchema::isAll($filter, $state) || $apply !== 'subrecord') {
                return;
            }

            $this->whereChildRows($query, $filter, function (QueryBuilder $sub) use ($filter, $state): void {
                $table = $this->assertIdent((string) $filter['subrecord_table']);
                $column = $this->assertIdent((string) $filter['column']);
                $qualified = $table.'.'.$column;

                if ($state['min'] !== null) {
                    $sub->where($qualified, '>=', $state['min']);
                }

                if ($state['max'] !== null) {
                    $sub->where($qualified, '<=', $state['max']);
                }
            });

            return;
        }

        $choice = ReportSchema::resolveSentinelChoice($filter, ReportSchema::choice($filter, $state));

        if ($choice === [] || ($choice['op'] ?? 'any') === 'any') {
            return;
        }

        if ($apply === 'presence') {
            $this->ensureQuestionsJoin($query);
            $this->wherePresence($query, $filter, $choice);

            return;
        }

        if ($apply === 'column_presence') {
            $this->whereColumnPresence($query, $filter, $choice);

            return;
        }

        if ($apply === 'voter') {
            $this->whereVoter($query, $filter, (string) ($choice['op'] ?? ''));

            return;
        }

        $expression = (string) ($filter['expression'] ?? '');

        if ($expression === '') {
            return;
        }

        if ($apply === 'pet') {
            $petConstraints[] = function ($petQuery) use ($expression, $choice, $state): void {
                $this->applyOp($petQuery, $expression, $choice, $state);
            };

            return;
        }

        if ($apply === 'subrecord') {
            $this->whereChildRows($query, $filter, function (QueryBuilder $sub) use ($expression, $choice, $state): void {
                $this->applyOp($sub, $expression, $choice, $state);
            });

            return;
        }

        if ($apply === 'hq') {
            $this->ensureQuestionsJoin($query);
        }

        $this->applyOp($query, $expression, $choice, $state);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filter
     * @param  list<int>  $ids
     */
    private function whereLookupColumn(Builder $query, array $filter, array $ids): void
    {
        $column = $this->assertIdent((string) $filter['column']);
        $apply = (string) ($filter['apply'] ?? 'household');

        if ($apply === 'hq') {
            $this->ensureQuestionsJoin($query);
            $query->whereIn('hq.'.$column, $ids);

            return;
        }

        if ($apply === 'resident') {
            $query->whereIn('resident.'.$column, $ids);

            return;
        }

        $query->whereIn('household.'.$column, $ids);
    }

    /**
     * Constrain residents by a child table without joining it onto the report
     * rows. A join would repeat a resident when that child table has more than
     * one row, and an inner join would drop residents who have no child row.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filter
     * @param  callable(QueryBuilder): void  $constrain
     */
    private function whereChildRows(Builder $query, array $filter, callable $constrain): void
    {
        $table = $this->assertIdent((string) $filter['subrecord_table']);
        $owner = $this->assertIdent((string) ($filter['subrecord_owner'] ?? 'resident_id'));
        $root = $this->assertIdent($query->getModel()->getTable());
        $key = $this->assertIdent($query->getModel()->getKeyName());
        $via = is_array($filter['subrecord_via'] ?? null) ? $filter['subrecord_via'] : null;

        $query->whereIn($root.'.'.$key, function (QueryBuilder $sub) use ($table, $owner, $constrain, $via): void {
            $sub->from($table);

            if ($via === null) {
                $sub->select($table.'.'.$owner);
            } else {
                $viaTable = $this->assertIdent((string) $via['table']);
                $from = $this->assertIdent((string) $via['from']);
                $to = $this->assertIdent((string) $via['to']);
                $viaOwner = $this->assertIdent((string) ($via['owner'] ?? $owner));

                $sub->select($viaTable.'.'.$viaOwner)
                    ->leftJoin($viaTable, $viaTable.'.'.$to, '=', $table.'.'.$from);
            }

            $constrain($sub);
        });
    }

    /**
     * Yes means the child column is present. No means the child row is missing
     * or the column is null, so residents without that row stay in the report.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filter
     * @param  array<string, mixed>  $choice
     */
    private function whereColumnPresence(Builder $query, array $filter, array $choice): void
    {
        $table = $this->assertIdent((string) $filter['subrecord_table']);
        $column = $this->assertIdent((string) $filter['column']);
        $owner = $this->assertIdent((string) ($filter['subrecord_owner'] ?? 'resident_id'));
        $root = $this->assertIdent($query->getModel()->getTable());
        $key = $this->assertIdent($query->getModel()->getKeyName());

        $present = function (QueryBuilder $sub) use ($table, $column, $owner, $root, $key): void {
            $sub->selectRaw('1')
                ->from($table)
                ->whereColumn($table.'.'.$owner, $root.'.'.$key)
                ->whereNotNull($table.'.'.$column)
                ->whereRaw('trim('.$table.'.'.$column.') <> \'\'');
        };

        if (($choice['op'] ?? '') === 'missing_or_null') {
            $query->whereNotExists($present);

            return;
        }

        $query->whereExists($present);
    }

    /**
     * A stored barangay name means registered. Blank, Yes, and No do not.
     * Local registration matches the official barangay name from system settings.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filter
     */
    private function whereVoter(Builder $query, array $filter, string $op): void
    {
        if (! in_array($op, ['voter_no', 'voter_yes', 'voter_local', 'voter_other'], true)) {
            $query->whereRaw('0 = 1');

            return;
        }

        $table = $this->assertIdent((string) $filter['subrecord_table']);
        $column = $this->assertIdent((string) $filter['column']);
        $owner = $this->assertIdent((string) ($filter['subrecord_owner'] ?? 'resident_id'));
        $root = $this->assertIdent($query->getModel()->getTable());
        $key = $this->assertIdent($query->getModel()->getKeyName());
        $qualified = $table.'.'.$column;
        $local = $this->localBarangayKey();
        $normalized = 'trim(lower(case when lower(trim('.$qualified.')) like \'barangay %\' then substring(trim('.$qualified.'), 10) else trim('.$qualified.') end))';

        $registered = function (QueryBuilder $sub) use ($table, $qualified, $owner, $root, $key): void {
            $sub->selectRaw('1')
                ->from($table)
                ->whereColumn($table.'.'.$owner, $root.'.'.$key)
                ->whereNotNull($qualified)
                ->whereRaw('trim('.$qualified.') <> \'\'')
                ->whereRaw('lower(trim('.$qualified.')) not in (\'yes\', \'no\')');
        };

        if ($op === 'voter_no') {
            $query->whereNotExists($registered);

            return;
        }

        $query->whereExists(function (QueryBuilder $sub) use ($registered, $op, $normalized, $local): void {
            $registered($sub);

            if ($op === 'voter_local') {
                $sub->whereRaw($normalized.' = ?', [$local]);
            }

            if ($op === 'voter_other') {
                $sub->whereRaw($normalized.' <> ?', [$local]);
            }
        });
    }

    /**
     * Official barangay name with a leading "Barangay " removed, lowercased.
     */
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
     * Infant and women reports keep every qualifying resident, including those
     * with no child row. The child columns stay blank through eager loading.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $category
     */
    private function applyAudience(Builder $query, array $category): void
    {
        $audience = $category['audience'] ?? null;

        if ($audience === 'infant') {
            $query->whereRaw('timestampdiff(MONTH, `resident`.`date_of_birth`, curdate()) between 0 and 11');

            return;
        }

        if ($audience === 'women') {
            $query->where('resident.sex_id', Sex::FEMALE)
                ->whereRaw('timestampdiff(YEAR, `resident`.`date_of_birth`, curdate()) between 10 and 54');
        }
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filter
     * @param  list<int>  $ids
     */
    private function whereJunction(Builder $query, array $filter, array $ids): void
    {
        $table = $this->assertIdent((string) $filter['junction_table']);
        $owner = $this->assertIdent((string) $filter['junction_owner']);
        $lookup = $this->assertIdent((string) $filter['junction_id']);

        $query->whereExists(function (QueryBuilder $sub) use ($table, $owner, $lookup, $ids): void {
            $sub->selectRaw('1')
                ->from($table)
                ->whereColumn($table.'.'.$owner, 'hq.household_questions_id')
                ->whereIn($table.'.'.$lookup, $ids);
        });
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filter
     * @param  array<string, mixed>  $choice
     */
    private function wherePresence(Builder $query, array $filter, array $choice): void
    {
        $table = $this->assertIdent((string) $filter['presence_table']);
        $owner = $this->assertIdent((string) $filter['presence_owner']);
        $query->whereNotNull('hq.household_questions_id');

        $exists = function (QueryBuilder $sub) use ($table, $owner): void {
            $sub->selectRaw('1')
                ->from($table)
                ->whereColumn($table.'.'.$owner, 'hq.household_questions_id');
        };

        if (($choice['op'] ?? '') === 'missing') {
            $query->whereNotExists($exists);

            return;
        }

        $query->whereExists($exists);
    }

    /**
     * @param  Builder<Model>|QueryBuilder|Relation  $query
     * @param  array<string, mixed>  $choice
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     */
    private function applyOp(object $query, string $expression, array $choice, array $state): void
    {
        $expression = $this->assertExpression($expression);
        $op = (string) ($choice['op'] ?? 'any');

        if ($op === 'eq' || $op === 'neq') {
            if (! is_numeric($choice['operand'] ?? null)) {
                $query->whereRaw('0 = 1');

                return;
            }

            $comparator = $op === 'eq' ? '=' : '<>';
            $query->whereRaw($expression.' '.$comparator.' ?', [$choice['operand']]);

            return;
        }

        if ($op === 'gt') {
            $query->whereRaw($expression.' > ?', [$choice['operand']]);

            return;
        }

        if ($op === 'eq_input') {
            $query->whereRaw($expression.' = ?', [(int) $state['value']]);

            return;
        }

        if ($op === 'in') {
            $operands = array_map(intval(...), (array) ($choice['operand'] ?? []));

            if ($operands === []) {
                $query->whereRaw('1 = 0');

                return;
            }

            $placeholders = implode(', ', array_fill(0, count($operands), '?'));
            $query->whereRaw($expression.' in ('.$placeholders.')', $operands);

            return;
        }

        if ($op === 'not_null') {
            $query->whereRaw($expression.' is not null');

            return;
        }

        if ($op === 'within_months' || $op === 'older_than_months') {
            $cutoff = now()->subMonths((int) ($choice['months'] ?? 12))->toDateString();
            $comparator = $op === 'within_months' ? '>=' : '<';
            $query->whereRaw($expression.' is not null')
                ->whereRaw($expression.' '.$comparator.' ?', [$cutoff]);
        }
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     * @param  array<string, callable(Builder): void>  $relationConstraints
     */
    private function rememberRelationConstraint(array $filter, array $state, array &$relationConstraints): void
    {
        $relation = $filter['constrain_relation'] ?? null;

        if (! is_string($relation) || $relation === '') {
            return;
        }

        $ids = ReportSchema::isAll($filter, $state) ? null : $state['ids'];
        $column = (string) ($filter['constrain_column'] ?? '');
        $order = (string) ($filter['constrain_order'] ?? '');

        $relationConstraints[$relation] = function ($related) use ($ids, $column, $order): void {
            if ($ids !== null && $column !== '') {
                $related->whereIn($column, $ids);
            }

            if ($order !== '') {
                $related->orderBy($order);
            }
        };
    }

    /**
     * @param  Builder<Model>  $query
     */
    private function ensureQuestionsJoin(Builder $query): void
    {
        foreach ($query->getQuery()->joins ?? [] as $join) {
            if ($join->table === 'household_questions as hq') {
                return;
            }
        }

        $query->leftJoin('household_questions as hq', function ($join): void {
            $join->on('hq.household_id', '=', 'household.household_id')
                ->whereRaw(
                    'hq.household_questions_id = (select max(`household_questions_id`) from `household_questions` where `household_id` = `household`.`household_id`)'
                );
        });
    }

    /**
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $filters
     */
    private function petsAreRestricted(array $category, array $filters): bool
    {
        foreach ($category['filters'] ?? [] as $filter) {
            if (! is_array($filter) || ($filter['apply'] ?? '') !== 'pet') {
                continue;
            }

            $state = ReportSchema::filterState($filters, (string) $filter['key']);

            if (! ReportSchema::isAll($filter, $state)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  list<array<string, mixed>>  $columns
     * @param  list<callable(Builder|QueryBuilder): void>  $petConstraints
     * @param  array<string, callable(Builder): void>  $relationConstraints
     */
    private function applySelectedRelations(Builder $query, array $columns, array $petConstraints, array $relationConstraints): void
    {
        $loads = ReportSchema::loadsFor($columns);
        $with = [];

        foreach ($loads['with'] as $path) {
            if ($path === 'residents' || str_ends_with($path, '.residents')) {
                $with[$path] = fn ($residents) => $residents
                    ->orderBy('last_name')
                    ->orderBy('first_name');

                continue;
            }

            if ($path === 'pets') {
                $with['pets'] = function ($pets) use ($petConstraints): void {
                    foreach ($petConstraints as $apply) {
                        $apply($pets);
                    }

                    $pets->orderByRaw('(select `specie` from `specie` where `specie`.`specie_id` = `pet_census`.`specie_id`) asc')
                        ->orderByRaw('(select `breed` from `breed` where `breed`.`breed_id` = `pet_census`.`breed_id`) asc')
                        ->orderBy('pet_census_id');
                };

                continue;
            }

            if (isset($relationConstraints[$path])) {
                $with[$path] = $relationConstraints[$path];

                continue;
            }

            $with[] = $path;
        }

        $nestedCounts = [];

        foreach ($loads['withCount'] as $path) {
            if ($path === 'pets') {
                $query->withCount(['pets as pets_count' => function ($pets) use ($petConstraints): void {
                    foreach ($petConstraints as $apply) {
                        $apply($pets);
                    }
                }]);

                continue;
            }

            if (! str_contains($path, '.')) {
                $query->withCount($path);

                continue;
            }

            $parent = Str::beforeLast($path, '.');
            $relation = Str::afterLast($path, '.');
            $nestedCounts[$parent][] = $relation;
        }

        foreach ($nestedCounts as $parent => $relations) {
            $existing = $with[$parent] ?? null;
            $with[$parent] = function ($related) use ($existing, $relations): void {
                if (is_callable($existing)) {
                    $existing($related);
                }

                $related->withCount($relations);
            };
        }

        if ($with !== []) {
            $query->with($with);
        }
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $filters
     * @param  array<string, int>  $presentedKeys
     * @param  list<callable(Builder|QueryBuilder): void>  $petConstraints
     */
    private function applyCounts(Builder $query, array $category, array $filters, array $presentedKeys, array $petConstraints): void
    {
        foreach ($category['counts'] ?? [] as $count) {
            if (! is_array($count) || ! isset($presentedKeys[$count['column'] ?? ''])) {
                continue;
            }

            if (($count['type'] ?? '') === 'pet') {
                $query->withCount(['pets as '.$this->assertIdent((string) $count['alias']) => function ($pets) use ($petConstraints): void {
                    foreach ($petConstraints as $apply) {
                        $apply($pets);
                    }
                }]);

                continue;
            }

            if (($count['type'] ?? '') !== 'junction') {
                continue;
            }

            $table = $this->assertIdent((string) $count['junction_table']);
            $owner = $this->assertIdent((string) $count['junction_owner']);
            $lookup = $this->assertIdent((string) $count['junction_id']);
            $filterKey = (string) ($count['filter'] ?? '');
            $filter = ReportSchema::filterDefinition($category, $filterKey);
            $state = ReportSchema::filterState($filters, $filterKey);

            $sub = DB::table($table)
                ->selectRaw('count(*)')
                ->whereRaw(
                    $this->quote($table).'.'.$this->quote($owner)
                    .' = (select max(`household_questions_id`) from `household_questions` where `household_id` = `household`.`household_id`)'
                );

            if (! ReportSchema::isAll($filter, $state)) {
                $sub->whereIn($table.'.'.$lookup, $state['ids']);
            }

            $query->addSelect([$this->assertIdent((string) $count['alias']) => $sub]);
        }
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $filters
     * @param  list<string>  $selectedColumns
     * @param  list<callable(Builder|QueryBuilder): void>  $petConstraints
     */
    private function applySorts(Builder $query, string $categoryKey, array $filters, array $selectedColumns, array $petConstraints): void
    {
        foreach (ReportSchema::activeSorts($categoryKey, $filters, $selectedColumns) as $active) {
            $sort = $active['sort'];
            $type = (string) ($sort['type'] ?? '');

            if ($type === 'lookup') {
                $this->applyLookupSort($query, $sort);

                continue;
            }

            if ($type === 'expression') {
                $query->orderByRaw($this->assertExpression((string) $sort['sql']).' asc');

                continue;
            }

            if ($type === 'pet_min') {
                $this->applyPetMinSort($query, $sort, $petConstraints);

                continue;
            }

            if ($type === 'pet_column') {
                $this->applyPetColumnSort($query, $sort, $petConstraints);

                continue;
            }

            if ($type === 'junction_min') {
                $this->applyJunctionMinSort($query, $sort, $active['state'], $active['filter']);

                continue;
            }

            if ($type === 'child_lookup') {
                $this->applyChildLookupSort($query, $sort);
            }
        }
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $sort
     */
    private function applyLookupSort(Builder $query, array $sort): void
    {
        $left = (string) $sort['left'];

        if (str_starts_with($left, 'hq.')) {
            $this->ensureQuestionsJoin($query);
        }

        $alias = $this->assertIdent((string) $sort['alias']);
        $table = $this->assertIdent((string) $sort['table']);
        $idColumn = $this->assertIdent((string) $sort['id_column']);
        $labelColumn = $this->assertIdent((string) $sort['label_column']);
        $left = $this->assertQualified($left);

        $query->leftJoin($table.' as '.$alias, $alias.'.'.$idColumn, '=', $left);
        $query->orderByRaw(
            'COALESCE('.$this->quote($alias).'.'.$this->quote($labelColumn).', ?) asc',
            ['Not Answered'],
        );
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $sort
     * @param  list<callable(Builder|QueryBuilder): void>  $petConstraints
     */
    private function applyPetMinSort(Builder $query, array $sort, array $petConstraints): void
    {
        $lookupTable = $this->assertIdent((string) $sort['lookup_table']);
        $lookupId = $this->assertIdent((string) $sort['lookup_id']);
        $lookupLabel = $this->assertIdent((string) $sort['lookup_label']);
        $fk = $this->assertIdent((string) $sort['fk']);

        $sub = DB::table('pet_census')
            ->selectRaw('min('.$this->quote($lookupTable).'.'.$this->quote($lookupLabel).')')
            ->leftJoin($lookupTable, $lookupTable.'.'.$lookupId, '=', 'pet_census.'.$fk)
            ->whereColumn('pet_census.household_id', 'household.household_id');

        foreach ($petConstraints as $apply) {
            $apply($sub);
        }

        $query->orderByRaw('COALESCE(('.$sub->toSql().'), ?) asc', [...$sub->getBindings(), 'Not Answered']);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $sort
     * @param  list<callable(Builder|QueryBuilder): void>  $petConstraints
     */
    private function applyPetColumnSort(Builder $query, array $sort, array $petConstraints): void
    {
        $column = $this->assertIdent((string) $sort['column']);
        $sub = DB::table('pet_census')
            ->selectRaw('min('.$this->quote('pet_census').'.'.$this->quote($column).')')
            ->whereColumn('pet_census.household_id', 'household.household_id');

        foreach ($petConstraints as $apply) {
            $apply($sub);
        }

        $query->orderByRaw('('.$sub->toSql().') asc', $sub->getBindings());
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $sort
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     * @param  array<string, mixed>  $filter
     */
    private function applyJunctionMinSort(Builder $query, array $sort, array $state, array $filter): void
    {
        $junction = $this->assertIdent((string) $sort['junction_table']);
        $owner = $this->assertIdent((string) $sort['junction_owner']);
        $junctionId = $this->assertIdent((string) $sort['junction_id']);
        $lookupTable = $this->assertIdent((string) $sort['lookup_table']);
        $lookupId = $this->assertIdent((string) $sort['lookup_id']);
        $lookupLabel = $this->assertIdent((string) $sort['lookup_label']);

        $sub = DB::table($junction)
            ->selectRaw('min('.$this->quote($lookupTable).'.'.$this->quote($lookupLabel).')')
            ->leftJoin($lookupTable, $lookupTable.'.'.$lookupId, '=', $junction.'.'.$junctionId)
            ->whereRaw(
                $this->quote($junction).'.'.$this->quote($owner)
                .' = (select max(`household_questions_id`) from `household_questions` where `household_id` = `household`.`household_id`)'
            );

        if (! ReportSchema::isAll($filter, $state)) {
            $sub->whereIn($junction.'.'.$junctionId, $state['ids']);
        }

        $query->orderByRaw('COALESCE(('.$sub->toSql().'), ?) asc', [...$sub->getBindings(), 'Not Answered']);
    }

    /**
     * One label per resident, taken from the child row. MIN keeps a single
     * report row when a resident has more than one child record.
     *
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $sort
     */
    private function applyChildLookupSort(Builder $query, array $sort): void
    {
        $child = $this->assertIdent((string) $sort['child_table']);
        $owner = $this->assertIdent((string) $sort['child_owner']);
        $childFk = $this->assertIdent((string) $sort['child_fk']);
        $lookupTable = $this->assertIdent((string) $sort['lookup_table']);
        $lookupId = $this->assertIdent((string) $sort['lookup_id']);
        $lookupLabel = $this->assertIdent((string) $sort['lookup_label']);
        $root = $this->assertIdent($query->getModel()->getTable());
        $key = $this->assertIdent($query->getModel()->getKeyName());

        $sub = DB::table($child)
            ->selectRaw('min('.$this->quote($lookupTable).'.'.$this->quote($lookupLabel).')')
            ->leftJoin($lookupTable, $lookupTable.'.'.$lookupId, '=', $child.'.'.$childFk);

        $viaTable = $sort['via_table'] ?? null;

        if (is_string($viaTable) && $viaTable !== '') {
            $via = $this->assertIdent($viaTable);
            $from = $this->assertIdent((string) $sort['via_from']);
            $to = $this->assertIdent((string) $sort['via_to']);
            $viaOwner = $this->assertIdent((string) $sort['via_owner']);

            $sub->leftJoin($via, $via.'.'.$to, '=', $child.'.'.$from)
                ->whereColumn($via.'.'.$viaOwner, $root.'.'.$key);
        } else {
            $sub->whereColumn($child.'.'.$owner, $root.'.'.$key);
        }

        $query->orderByRaw('COALESCE(('.$sub->toSql().'), ?) asc', [...$sub->getBindings(), 'Not Answered']);
    }

    /**
     * @return array{id: int, label: string}
     */
    private function optionPayload(Model $option, Model $prototype, string $labelColumn, string $idColumn): array
    {
        $id = $idColumn !== '' ? $option->getAttribute($idColumn) : $option->getAttribute($prototype->getKeyName());

        return [
            'id' => (int) $id,
            'label' => (string) $option->getAttribute($labelColumn),
        ];
    }

    private function likeContains(string $term): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);

        return '%'.$escaped.'%';
    }

    private function assertIdent(string $value): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid report identifier.');
        }

        return $value;
    }

    private function assertQualified(string $value): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*\.[A-Za-z_][A-Za-z0-9_]*$/', $value) !== 1) {
            throw new InvalidArgumentException('Invalid report column.');
        }

        return $value;
    }

    private function assertExpression(string $value): string
    {
        $plain = '/^[A-Za-z_][A-Za-z0-9_]*\.[A-Za-z_][A-Za-z0-9_]*$/';
        $coalesce = '/^COALESCE\([A-Za-z_][A-Za-z0-9_]*\.[A-Za-z_][A-Za-z0-9_]*, 0\)$/';

        if (preg_match($plain, $value) !== 1 && preg_match($coalesce, $value) !== 1) {
            throw new InvalidArgumentException('Invalid report expression.');
        }

        return $value;
    }

    private function quote(string $ident): string
    {
        return '`'.$this->assertIdent($ident).'`';
    }
}
