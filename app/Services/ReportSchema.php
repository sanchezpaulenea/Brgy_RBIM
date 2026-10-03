<?php

namespace App\Services;

use App\Models\HouseholdManagement\Household;
use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Reads report category and column config for the report engine.
 */
class ReportSchema
{
    public const OPTION_LIMIT = 5;

    public const PREVIEW_PER_PAGE = 25;

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function categories(): array
    {
        return config('report_categories', []);
    }

    /**
     * @return array<string, mixed>
     */
    public static function category(string $key): array
    {
        $category = config("report_categories.{$key}");

        if (! is_array($category)) {
            throw new InvalidArgumentException("Unknown report category [{$key}].");
        }

        return $category;
    }

    /**
     * @param  array<string, mixed>  $category
     * @return array<string, mixed>
     */
    public static function filterDefinition(array $category, string $key): array
    {
        foreach ($category['filters'] ?? [] as $filter) {
            if (is_array($filter) && ($filter['key'] ?? null) === $key) {
                return $filter;
            }
        }

        throw new InvalidArgumentException("Unknown report filter [{$key}].");
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function columns(string $level): array
    {
        $columns = config("report_columns.{$level}", []);

        return is_array($columns) ? array_values($columns) : [];
    }

    /**
     * Level columns plus the category's extra column groups.
     *
     * @return list<array<string, mixed>>
     */
    public static function columnsFor(string $categoryKey): array
    {
        $category = self::category($categoryKey);
        $columns = self::columns((string) $category['level']);
        $extras = config('report_columns.extras', []);

        foreach ($category['extra_columns'] ?? [] as $key) {
            if (is_string($key) && isset($extras[$key]) && is_array($extras[$key])) {
                $columns[] = $extras[$key];
            }
        }

        return $columns;
    }

    /**
     * @param  list<string>  $keys
     * @param  array<string, mixed>  $filterInput
     * @return list<array<string, mixed>>
     */
    public static function presentColumns(string $categoryKey, array $keys, array $filterInput): array
    {
        $category = self::category($categoryKey);
        $selected = array_flip($keys);
        $hidden = [];
        $forced = [];

        foreach ($category['filters'] ?? [] as $filter) {
            if (! is_array($filter) || ! self::filterApplies($category, $filter, $filterInput)) {
                continue;
            }

            $state = self::filterState($filterInput, (string) $filter['key']);
            $owns = is_array($filter['owns'] ?? null) ? $filter['owns'] : [];

            foreach ($owns as $owned) {
                if (! is_string($owned) || $owned === '') {
                    continue;
                }

                if (self::isSingular($filter, $state)) {
                    $hidden[$owned] = true;
                } elseif (($filter['force_owned'] ?? true) !== false) {
                    $forced[$owned] = true;
                }
            }
        }

        $presented = [];

        foreach (self::columnsFor($categoryKey) as $column) {
            $key = (string) ($column['key'] ?? '');

            if ($key === '' || isset($hidden[$key])) {
                continue;
            }

            if (($column['is_group'] ?? false) === true) {
                $children = [];
                $groupSelected = isset($selected[$key]);

                foreach ($column['columns'] ?? [] as $child) {
                    if (! is_array($child)) {
                        continue;
                    }

                    $childKey = (string) ($child['key'] ?? '');
                    $qualified = $key.'.'.$childKey;

                    if ($childKey === '' || isset($hidden[$qualified]) || isset($hidden[$childKey])) {
                        continue;
                    }

                    if ($groupSelected || isset($forced[$qualified]) || isset($forced[$childKey])) {
                        $children[] = $child;
                    }
                }

                if ($children === []) {
                    continue;
                }

                $column['columns'] = $children;
                $presented[] = $column;

                continue;
            }

            if (isset($selected[$key]) || isset($forced[$key])) {
                $presented[] = $column;
            }
        }

        return $presented;
    }

    /**
     * @param  list<array<string, mixed>>  $columns
     * @return list<string>
     */
    public static function flatKeys(array $columns): array
    {
        $keys = [];

        foreach ($columns as $column) {
            $keys[] = (string) $column['key'];

            foreach ($column['columns'] ?? [] as $child) {
                if (is_array($child) && isset($child['key'])) {
                    $keys[] = (string) $column['key'].'.'.(string) $child['key'];
                }
            }
        }

        return $keys;
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{mode: string, ids: list<int>, value: int|string|null, min: ?string, max: ?string}
     */
    public static function filterState(array $input, string $key): array
    {
        $state = $input[$key] ?? [];

        if (! is_array($state)) {
            $state = [];
        }

        $ids = [];

        foreach ($state['ids'] ?? [] as $id) {
            if (is_numeric($id)) {
                $ids[] = (int) $id;
            }
        }

        $value = $state['value'] ?? null;

        if ($value === '' || $value === null) {
            $value = null;
        } elseif (is_numeric($value)) {
            $value = (int) $value;
        } elseif (! is_string($value)) {
            $value = null;
        }

        return [
            'mode' => is_string($state['mode'] ?? null) ? $state['mode'] : '',
            'ids' => $ids,
            'value' => $value,
            'min' => self::bound($state['min'] ?? null),
            'max' => self::bound($state['max'] ?? null),
        ];
    }

    private static function bound(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     * @return array<string, mixed>
     */
    public static function choice(array $filter, array $state): array
    {
        foreach ($filter['choices'] ?? [] as $choice) {
            if (is_array($choice) && ($choice['value'] ?? null) === $state['mode']) {
                return $choice;
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     */
    public static function isAll(array $filter, array $state): bool
    {
        if (($filter['type'] ?? '') === 'range') {
            return ($state['min'] ?? null) === null && ($state['max'] ?? null) === null;
        }

        if (($filter['type'] ?? '') === 'lookup') {
            return $state['mode'] === 'all';
        }

        return (self::choice($filter, $state)['op'] ?? '') === 'any';
    }

    /**
     * A sub-filter stays out of the query when its paired answer does not apply.
     *
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $filter
     * @param  array<string, mixed>  $filterInput
     */
    public static function filterApplies(array $category, array $filter, array $filterInput): bool
    {
        $when = $filter['visible_when'] ?? null;

        if (! is_array($when)) {
            return true;
        }

        $state = self::filterState($filterInput, (string) ($when['filter'] ?? ''));
        $except = $when['except_modes'] ?? [];

        if (is_array($except) && in_array($state['mode'], $except, true)) {
            return false;
        }

        if (($when['unless_sentinel'] ?? false) === true) {
            return self::passesUnlessSentinel($category, $when, $filterInput);
        }

        if (($when['only_sentinel'] ?? false) === true) {
            try {
                $other = self::filterDefinition($category, (string) ($when['filter'] ?? ''));
            } catch (InvalidArgumentException) {
                return false;
            }

            return self::selectionIsOnlySentinel($other, $state);
        }

        $include = $when['include_ids'] ?? null;

        if (is_array($include)) {
            $needed = array_map(intval(...), $include);

            foreach ($state['ids'] as $id) {
                if (in_array($id, $needed, true)) {
                    return true;
                }
            }

            return false;
        }

        $allowed = $when['lookup_ids'] ?? null;

        if (! is_array($allowed)) {
            return true;
        }

        if (! in_array($state['mode'], ['one', 'multiple'], true) || $state['ids'] === []) {
            return false;
        }

        $allowedIds = array_map(intval(...), $allowed);

        foreach ($state['ids'] as $id) {
            if (! in_array($id, $allowedIds, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Facility visit reason stays hidden while the facility filter is None,
     * or while a specific facility has not been chosen yet.
     *
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $when
     * @param  array<string, mixed>  $filterInput
     */
    public static function passesUnlessSentinel(array $category, array $when, array $filterInput): bool
    {
        try {
            $other = self::filterDefinition($category, (string) ($when['filter'] ?? ''));
        } catch (InvalidArgumentException) {
            return false;
        }

        $state = self::filterState($filterInput, (string) ($when['filter'] ?? ''));

        if ($state['mode'] === 'all') {
            return true;
        }

        if (self::isSentinelMode($other, $state) || self::selectionIsOnlySentinel($other, $state)) {
            return false;
        }

        return in_array($state['mode'], ['one', 'multiple'], true) && $state['ids'] !== [];
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     */
    public static function isSentinelMode(array $filter, array $state): bool
    {
        $mode = $filter['sentinel_mode'] ?? null;

        return is_string($mode) && $mode !== '' && $state['mode'] === $mode;
    }

    /**
     * @param  array<string, mixed>  $filter
     */
    public static function sentinelId(array $filter): ?int
    {
        $model = $filter['model'] ?? $filter['sentinel_model'] ?? null;

        if (! is_string($model) || ! method_exists($model, 'noneId')) {
            return null;
        }

        $id = $model::noneId();

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     * @return list<int>
     */
    public static function selectedLookupIds(array $filter, array $state): array
    {
        if (! self::isSentinelMode($filter, $state)) {
            return $state['ids'];
        }

        $id = self::sentinelId($filter);

        return $id === null ? [] : [$id];
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     */
    public static function selectionIsOnlySentinel(array $filter, array $state): bool
    {
        if (self::isSentinelMode($filter, $state)) {
            return true;
        }

        $id = self::sentinelId($filter);

        if ($id === null || ! in_array($state['mode'], ['one', 'multiple'], true) || $state['ids'] === []) {
            return false;
        }

        foreach ($state['ids'] as $selected) {
            if ($selected !== $id) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array<string, mixed>  $choice
     * @return array<string, mixed>
     */
    public static function resolveSentinelChoice(array $filter, array $choice): array
    {
        $op = (string) ($choice['op'] ?? '');

        if ($op !== 'is_sentinel' && $op !== 'not_sentinel') {
            return $choice;
        }

        $choice['op'] = $op === 'is_sentinel' ? 'eq' : 'neq';
        $choice['operand'] = self::sentinelId($filter);

        return $choice;
    }

    /**
     * True when the sub-filter resolves to exactly one value.
     *
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     */
    public static function isSingular(array $filter, array $state): bool
    {
        if (($filter['type'] ?? '') === 'range') {
            return false;
        }

        if (($filter['type'] ?? '') === 'lookup') {
            if ($state['mode'] === 'one' || self::isSentinelMode($filter, $state)) {
                return true;
            }

            return $state['mode'] === 'multiple' && count($state['ids']) === 1;
        }

        return (bool) (self::choice($filter, $state)['singular'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $filterInput
     */
    public static function includesUnanswered(array $category, array $filterInput): bool
    {
        if (($category['questions'] ?? false) !== true) {
            return false;
        }

        foreach ($category['filters'] ?? [] as $filter) {
            if (! is_array($filter) || ! self::filterApplies($category, $filter, $filterInput)) {
                continue;
            }

            $state = self::filterState($filterInput, (string) $filter['key']);

            if (! self::isAll($filter, $state)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sorts for owned columns that are actually present in the report.
     *
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $filterInput
     * @param  list<string>  $selectedColumns
     * @return list<array{sort: array<string, mixed>, filter: array<string, mixed>, state: array{mode: string, ids: list<int>, value: int|string|null}}>
     */
    public static function activeSorts(string $categoryKey, array $filterInput, array $selectedColumns): array
    {
        $category = self::category($categoryKey);
        $visible = array_flip(self::flatKeys(self::presentColumns($categoryKey, $selectedColumns, $filterInput)));
        $sorts = [];

        foreach ($category['filters'] ?? [] as $filter) {
            if (! is_array($filter) || ! is_array($filter['sort'] ?? null) || ! self::filterApplies($category, $filter, $filterInput)) {
                continue;
            }

            $shown = false;

            foreach ($filter['owns'] ?? [] as $owned) {
                if (is_string($owned) && isset($visible[$owned])) {
                    $shown = true;
                }
            }

            if (! $shown) {
                continue;
            }

            $sorts[] = [
                'sort' => $filter['sort'],
                'filter' => $filter,
                'state' => self::filterState($filterInput, (string) $filter['key']),
            ];
        }

        return $sorts;
    }

    /**
     * @return class-string<Model>
     */
    public static function levelModel(string $level): string
    {
        return match ($level) {
            'household' => Household::class,
            'resident' => Resident::class,
            default => throw new InvalidArgumentException("Unknown report level [{$level}]."),
        };
    }

    /**
     * Relations to eager-load for the presented columns. Has-many data stays
     * on the root row; only the relations those columns read are loaded.
     *
     * @param  list<array<string, mixed>>  $columns
     * @return array{with: list<string>, withCount: list<string>}
     */
    public static function loadsFor(array $columns): array
    {
        $with = [];
        $counts = [];

        foreach ($columns as $column) {
            self::collectLoads($column, '', $with, $counts);
        }

        return [
            'with' => array_values(array_unique(array_filter($with))),
            'withCount' => array_values(array_unique(array_filter($counts))),
        ];
    }

    /**
     * @param  array<string, mixed>  $column
     * @param  list<string>  $with
     * @param  list<string>  $counts
     */
    private static function collectLoads(array $column, string $prefix, array &$with, array &$counts): void
    {
        $format = self::format($column);
        $path = self::qualify($prefix, (string) ($column['source'] ?? ''));

        if ($format === 'count') {
            if (($column['count_via'] ?? '') !== 'repository' && $path !== '') {
                $counts[] = $path;
            }

            return;
        }

        if ($format === 'group') {
            if ($path !== '') {
                $with[] = $path;
            }

            foreach ($column['columns'] ?? [] as $child) {
                if (is_array($child)) {
                    self::collectLoads($child, $path, $with, $counts);
                }
            }

            return;
        }

        if ($format === 'records') {
            if ($path !== '') {
                $with[] = $path;
            }

            foreach ($column['fields'] ?? [] as $field) {
                $fieldSource = (string) ($field['source'] ?? '');

                if (str_contains($fieldSource, '.')) {
                    $with[] = self::qualify($path, Str::beforeLast($fieldSource, '.'));
                }
            }

            return;
        }

        if (in_array($format, ['pluck', 'person_name', 'person_names', 'age'], true)) {
            if ($path !== '') {
                $with[] = $path;
            }

            return;
        }

        if (str_contains($path, '.')) {
            $with[] = Str::beforeLast($path, '.');
        }
    }

    /**
     * @param  array<string, mixed>  $column
     */
    public static function format(array $column): string
    {
        if (($column['is_group'] ?? false) === true) {
            return 'group';
        }

        $format = $column['format'] ?? 'value';

        return is_string($format) && $format !== '' ? $format : 'value';
    }

    private static function qualify(string $prefix, string $source): string
    {
        if ($source === '') {
            return $prefix;
        }

        if ($prefix === '') {
            return $source;
        }

        return $prefix.'.'.$source;
    }
}
