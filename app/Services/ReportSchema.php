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
     * @return list<array<string, mixed>>
     */
    public static function columns(string $level): array
    {
        $columns = config("report_columns.{$level}", []);

        return is_array($columns) ? array_values($columns) : [];
    }

    /**
     * @param  list<string>  $keys
     * @return list<array<string, mixed>>
     */
    public static function selectedColumns(string $level, array $keys): array
    {
        $selected = array_flip($keys);

        return array_values(array_filter(
            self::columns($level),
            fn (array $column): bool => isset($selected[$column['key']]),
        ));
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
     * Relations to eager-load for the selected columns. Has-many data stays
     * on the root row; only the relations those columns read are loaded.
     *
     * @param  list<string>  $keys
     * @return array{with: list<string>, withCount: list<string>}
     */
    public static function loads(string $level, array $keys): array
    {
        $with = [];
        $counts = [];

        foreach (self::selectedColumns($level, $keys) as $column) {
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
            $counts[] = $path;

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
