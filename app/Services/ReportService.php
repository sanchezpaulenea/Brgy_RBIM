<?php

namespace App\Services;

use App\Exports\ReportWorkbookExport;
use App\Repositories\Interfaces\ReportRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ReportService
{
    public function __construct(
        protected ReportRepositoryInterface $reportRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getCategoryDefinition(string $key): array
    {
        return ReportSchema::category($key);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listCategories(): array
    {
        $categories = [];

        foreach (ReportSchema::categories() as $key => $category) {
            $categories[] = $this->publicCategory((string) $key, $category);
        }

        return $categories;
    }

    /**
     * @return list<array{id: int, label: string}>
     */
    public function getFilterOptions(string $key, ?string $search): array
    {
        $this->getCategoryDefinition($key);

        return $this->reportRepository->getCategoryOptions($key, $search);
    }

    /**
     * @param  array<string, mixed>  $filterInput
     * @param  list<string>  $selectedColumns
     * @return array<string, mixed>
     */
    public function preview(array $filterInput, array $selectedColumns): array
    {
        return $this->assemble($filterInput, $selectedColumns, paginate: true);
    }

    /**
     * @param  array<string, mixed>  $filterInput
     * @param  list<string>  $selectedColumns
     */
    public function export(array $filterInput, array $selectedColumns, string $format, string $title): Response
    {
        $report = $this->assemble($filterInput, $selectedColumns, paginate: false);
        $report['title'] = $title;
        $filename = Str::slug($title);

        if ($filename === '') {
            $filename = 'report';
        }

        $headings = $this->flatHeadings($report['columns']);
        $rows = array_map(
            fn (array $row): array => $this->flatRow($row['values'], $report['columns']),
            $report['rows'],
        );

        if ($format === 'pdf') {
            return Pdf::loadView('reports.master-list', [
                'title' => $report['title'],
                'subtitle' => $report['subtitle'],
                'headings' => $headings,
                'rows' => $rows,
            ])->setPaper('a4', 'landscape')->download($filename.'.pdf');
        }

        return Excel::download(
            new ReportWorkbookExport($report['title'], $report['subtitle'], $headings, $rows),
            $filename.'.xlsx',
        );
    }

    /**
     * @param  array<string, mixed>  $filterInput
     * @param  list<string>  $selectedColumns
     * @return array<string, mixed>
     */
    private function assemble(array $filterInput, array $selectedColumns, bool $paginate): array
    {
        $categoryKey = (string) $filterInput['category'];
        $category = $this->getCategoryDefinition($categoryKey);
        $filter = is_array($filterInput['filter'] ?? null) ? $filterInput['filter'] : [];
        $columns = ReportSchema::selectedColumns($category['level'], $selectedColumns);
        $query = $this->reportRepository->buildReportQuery($categoryKey, $filter, $selectedColumns);
        $titles = $this->titles($categoryKey, $category, $filter);

        if ($paginate) {
            $paginator = $query->paginate(
                perPage: ReportSchema::PREVIEW_PER_PAGE,
                page: max(1, (int) ($filterInput['page'] ?? 1)),
            );
            $models = $paginator->getCollection();
            $pagination = [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ];
        } else {
            $models = $query->get();
            $pagination = null;
        }

        return [
            'title' => $titles['title'],
            'subtitle' => $titles['subtitle'],
            'columns' => array_map(fn (array $column): array => $this->publicColumn($column), $columns),
            'rows' => $models
                ->map(fn (Model $model): array => [
                    'id' => $model->getKey(),
                    'values' => $this->readRow($model, $columns),
                ])
                ->values()
                ->all(),
            'pagination' => $pagination,
        ];
    }

    /**
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $filter
     * @return array{title: string, subtitle: ?string}
     */
    private function titles(string $categoryKey, array $category, array $filter): array
    {
        $label = (string) $category['label'];
        $plural = Str::plural($label);
        $mode = (string) ($filter['mode'] ?? 'all');

        if ($mode === 'all') {
            return [
                'title' => "All {$plural} Master List",
                'subtitle' => null,
            ];
        }

        $ids = array_values(array_map('intval', $filter['ids'] ?? []));
        $names = array_map(
            fn (array $option): string => $option['label'].' '.$label,
            $this->reportRepository->filterLabels($categoryKey, $ids),
        );

        if ($mode === 'one') {
            return [
                'title' => "Per {$label} Master List",
                'subtitle' => $names[0] ?? null,
            ];
        }

        return [
            'title' => "Selected {$plural} Master List",
            'subtitle' => $names === [] ? null : implode(', ', $names),
        ];
    }

    /**
     * @param  array<string, mixed>  $category
     * @return array<string, mixed>
     */
    private function publicCategory(string $key, array $category): array
    {
        $label = (string) $category['label'];
        $plural = Str::plural($label);
        $excluded = $category['excluded_default_columns'] ?? [];
        $columns = [];

        foreach (ReportSchema::columns((string) $category['level']) as $column) {
            $columns[] = $this->publicColumn($column, ! in_array($column['key'], $excluded, true));
        }

        return [
            'key' => $key,
            'label' => $label,
            'level' => $category['level'],
            'filter_type' => $category['filter_type'],
            'filter_modes' => $category['filter_type'] === 'lookup'
                ? [
                    ['value' => 'all', 'label' => "All {$plural}"],
                    ['value' => 'one', 'label' => "Per {$label}"],
                    ['value' => 'multiple', 'label' => "Select 1+ {$plural}"],
                ]
                : [],
            'columns' => $columns,
        ];
    }

    /**
     * @param  array<string, mixed>  $column
     * @return array<string, mixed>
     */
    private function publicColumn(array $column, ?bool $default = null): array
    {
        $public = [
            'key' => $column['key'],
            'label' => $column['label'],
            'is_group' => (bool) ($column['is_group'] ?? false),
        ];

        if ($default !== null) {
            $public['default'] = $default;
        }

        if ($public['is_group']) {
            $public['columns'] = array_map(
                fn (array $child): array => [
                    'key' => $child['key'],
                    'label' => $child['label'],
                ],
                $column['columns'] ?? [],
            );
        }

        return $public;
    }

    /**
     * @param  list<array<string, mixed>>  $columns
     * @return array<string, mixed>
     */
    private function readRow(Model $model, array $columns): array
    {
        $values = [];

        foreach ($columns as $column) {
            $values[$column['key']] = $this->read($model, $column);
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $column
     */
    private function read(Model $model, array $column): mixed
    {
        $format = ReportSchema::format($column);
        $source = (string) ($column['source'] ?? '');
        $target = $source === '' ? $model : data_get($model, $source);

        return match ($format) {
            'count' => (int) ($model->{$source.'_count'} ?? 0),
            'person_name' => $this->personName($target),
            'person_names' => $this->personNames($target),
            'yes_no' => $this->yesNo($target),
            'date' => $this->formatDate($target),
            'age' => $target instanceof Model && method_exists($target, 'age') ? $target->age() : null,
            'pluck' => $this->pluckList($target, (string) ($column['pluck'] ?? '')),
            'records' => $this->recordLines($target, $column['fields'] ?? []),
            'group' => $this->readGroup($target, $column['columns'] ?? []),
            default => $this->scalar($target),
        };
    }

    /**
     * @param  list<array<string, mixed>>  $children
     */
    private function readGroup(mixed $related, array $children): mixed
    {
        if ($related instanceof Model) {
            return $this->readRow($related, $children);
        }

        if ($related instanceof Collection || is_array($related)) {
            return collect($related)
                ->filter(fn (mixed $item): bool => $item instanceof Model)
                ->map(fn (Model $item): array => $this->readRow($item, $children))
                ->values()
                ->all();
        }

        return null;
    }

    private function personName(mixed $person): ?string
    {
        if (! $person instanceof Model) {
            return null;
        }

        $givenNames = collect([
            $person->getAttribute('first_name'),
            $person->getAttribute('middle_name'),
            $person->getAttribute('suffix'),
        ])->filter(fn (mixed $part): bool => is_string($part) && $part !== '')->implode(' ');

        $lastName = (string) $person->getAttribute('last_name');

        if ($givenNames === '') {
            return $lastName !== '' ? $lastName : null;
        }

        return $lastName.', '.$givenNames;
    }

    /**
     * @return list<string>
     */
    private function personNames(mixed $people): array
    {
        return collect(is_array($people) || $people instanceof Collection ? $people : [])
            ->map(fn (mixed $person): ?string => $this->personName($person))
            ->filter(fn (?string $name): bool => $name !== null && $name !== '')
            ->values()
            ->all();
    }

    private function yesNo(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'Yes' : 'No';
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function pluckList(mixed $items, string $key): array
    {
        if ($key === '') {
            return [];
        }

        return collect(is_array($items) || $items instanceof Collection ? $items : [])
            ->map(fn (mixed $item): mixed => $item instanceof Model ? $item->getAttribute($key) : data_get($item, $key))
            ->filter(fn (mixed $value): bool => $value !== null && $value !== '')
            ->map(fn (mixed $value): string => (string) $value)
            ->values()
            ->all();
    }

    /**
     * @param  list<array{label?: string, source?: string}>  $fields
     * @return list<string>
     */
    private function recordLines(mixed $items, array $fields): array
    {
        return collect(is_array($items) || $items instanceof Collection ? $items : [])
            ->map(function (mixed $item) use ($fields): string {
                $parts = [];

                foreach ($fields as $field) {
                    $value = $this->scalar(data_get($item, (string) ($field['source'] ?? '')));

                    if ($value === null || $value === '') {
                        continue;
                    }

                    $parts[] = ($field['label'] ?? 'Detail').': '.$value;
                }

                return implode('; ', $parts);
            })
            ->filter(fn (string $line): bool => $line !== '')
            ->values()
            ->all();
    }

    private function scalar(mixed $value): mixed
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }

        if ($value instanceof Model || $value instanceof Collection || is_array($value)) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return $value;
    }

    /**
     * @param  list<array<string, mixed>>  $columns
     * @return list<string>
     */
    private function flatHeadings(array $columns): array
    {
        $headings = [];

        foreach ($columns as $column) {
            if (($column['is_group'] ?? false) === true) {
                foreach ($column['columns'] ?? [] as $child) {
                    $headings[] = $column['label'].' — '.$child['label'];
                }

                continue;
            }

            $headings[] = $column['label'];
        }

        return $headings;
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  list<array<string, mixed>>  $columns
     * @return list<string>
     */
    private function flatRow(array $values, array $columns): array
    {
        $cells = [];

        foreach ($columns as $column) {
            $value = $values[$column['key']] ?? null;

            if (($column['is_group'] ?? false) === true) {
                foreach ($column['columns'] ?? [] as $child) {
                    $cells[] = $this->exportCell($this->groupChild($value, $child['key']));
                }

                continue;
            }

            $cells[] = $this->exportCell($value);
        }

        return $cells;
    }

    private function groupChild(mixed $value, string $childKey): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) && array_is_list($value)) {
            return array_map(
                fn (mixed $item): mixed => is_array($item) ? ($item[$childKey] ?? null) : null,
                $value,
            );
        }

        if (is_array($value)) {
            return $value[$childKey] ?? null;
        }

        return null;
    }

    private function exportCell(mixed $value): string
    {
        if (is_array($value)) {
            $parts = array_values(array_filter(array_map(
                fn (mixed $item): string => is_scalar($item) ? (string) $item : '',
                $value,
            ), fn (string $item): bool => $item !== ''));

            return implode("\n", $parts);
        }

        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }
}
