<?php

namespace App\Services;

use App\Exports\ReportWorkbookExport;
use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
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
        protected AuditLogRepositoryInterface $auditLogRepository,
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
    public function getFilterOptions(string $key, string $filterKey, ?string $search, bool $all = false): array
    {
        $this->getCategoryDefinition($key);

        return $this->reportRepository->getCategoryOptions($key, $filterKey, $search, $all);
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
    public function export(
        array $filterInput,
        array $selectedColumns,
        string $format,
        string $title,
        ?string $subtitle,
        User $performedBy,
    ): Response {
        $report = $this->assemble($filterInput, $selectedColumns, paginate: false);
        $report['title'] = $title;
        $report['subtitle'] = $subtitle !== null && $subtitle !== '' ? $subtitle : null;
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
            $response = Pdf::loadView('reports.master-list', [
                'title' => $report['title'],
                'subtitle' => $report['subtitle'],
                'headings' => $headings,
                'rows' => $rows,
            ])->setPaper('a4', 'landscape')->download($filename.'.pdf');
        } else {
            $response = Excel::download(
                new ReportWorkbookExport($report['title'], $report['subtitle'], $headings, $rows),
                $filename.'.xlsx',
            );
        }

        $this->auditLogRepository->log(
            performedByUserId: $performedBy->user_id,
            actionId: Action::EXPORT,
            recordId: 0,
            description: 'Export report',
            oldValue: $this->exportFilterSummary($filterInput),
            newValue: $format,
            target: 'format',
            entity: 'report',
        );

        return $response;
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
        $filters = is_array($filterInput['filters'] ?? null) ? $filterInput['filters'] : [];
        $columns = ReportSchema::presentColumns($categoryKey, $selectedColumns, $filters);
        $query = $this->reportRepository->buildReportQuery($categoryKey, $filters, $selectedColumns);
        $titles = $this->titles($categoryKey, $category, $filters);
        $unanswered = $this->unansweredNote($category, $filters);

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
            'unanswered_households' => $unanswered['count'],
            'unanswered_note' => $unanswered['note'],
        ];
    }

    /**
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $filters
     * @return array{count: ?int, note: ?string}
     */
    private function unansweredNote(array $category, array $filters): array
    {
        if (($category['questions'] ?? false) !== true) {
            return ['count' => null, 'note' => null];
        }

        $count = $this->reportRepository->unansweredHouseholdCount();
        $noun = $count === 1 ? 'household has' : 'households have';
        $tail = ReportSchema::includesUnanswered($category, $filters)
            ? 'They are included in this list as Not Answered.'
            : 'They are excluded from this list.';

        return [
            'count' => $count,
            'note' => $count.' '.$noun.' no Household Questions answers. '.$tail,
        ];
    }

    /**
     * @param  array<string, mixed>  $category
     * @param  array<string, mixed>  $filters
     * @return array{title: string, subtitle: ?string}
     */
    private function titles(string $categoryKey, array $category, array $filters): array
    {
        $label = (string) $category['label'];
        $definitions = array_values(array_filter(
            $category['filters'] ?? [],
            fn (mixed $filter): bool => is_array($filter),
        ));
        $phrases = [];
        $allOpen = true;

        foreach ($definitions as $filter) {
            $state = ReportSchema::filterState($filters, (string) $filter['key']);

            if (! ReportSchema::isAll($filter, $state)) {
                $allOpen = false;
            }

            $phrases[] = $this->filterPhrase($categoryKey, $filter, $state);
        }

        if ($allOpen && count($definitions) === 1 && ($definitions[0]['type'] ?? '') === 'lookup') {
            $plural = $definitions[0]['plural'] ?? Str::plural((string) $definitions[0]['label']);

            return [
                'title' => "All {$plural} Master List",
                'subtitle' => null,
            ];
        }

        if ($allOpen) {
            return [
                'title' => "All {$label} Master List",
                'subtitle' => null,
            ];
        }

        if (count($definitions) === 1 && ($definitions[0]['type'] ?? '') === 'lookup') {
            $filter = $definitions[0];
            $state = ReportSchema::filterState($filters, (string) $filter['key']);
            $plural = $filter['plural'] ?? Str::plural((string) $filter['label']);
            $names = array_map(
                fn (string $name): string => $name.' '.$filter['label'],
                $this->selectedNames($categoryKey, $filter, $state),
            );

            if ($state['mode'] === 'one') {
                return [
                    'title' => "Per {$filter['label']} Master List",
                    'subtitle' => $names[0] ?? null,
                ];
            }

            return [
                'title' => "Select {$plural} Master List",
                'subtitle' => $names === [] ? null : implode(', ', $names),
            ];
        }

        return [
            'title' => "{$label} Master List",
            'subtitle' => $phrases === [] ? null : implode('; ', $phrases),
        ];
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     */
    private function filterPhrase(string $categoryKey, array $filter, array $state): string
    {
        $label = (string) $filter['label'];

        if (ReportSchema::isAll($filter, $state)) {
            $plural = $filter['plural'] ?? Str::plural($label);

            return $label.': All '.$plural;
        }

        if (($filter['type'] ?? '') === 'lookup') {
            $names = $this->selectedNames($categoryKey, $filter, $state);

            return $label.': '.($names === [] ? 'Selected' : implode(', ', $names));
        }

        $choice = ReportSchema::choice($filter, $state);
        $text = (string) ($choice['label'] ?? $label);

        if (($choice['op'] ?? '') === 'eq_input') {
            $text .= ' '.(string) ($state['value'] ?? '');
        }

        return $label.': '.$text;
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array{mode: string, ids: list<int>, value: int|string|null}  $state
     * @return list<string>
     */
    private function selectedNames(string $categoryKey, array $filter, array $state): array
    {
        return array_map(
            fn (array $option): string => $option['label'],
            $this->reportRepository->filterLabels($categoryKey, (string) $filter['key'], $state['ids']),
        );
    }

    /**
     * Category and sub-filters, kept inside audit_log.old_value's 45 characters.
     *
     * @param  array<string, mixed>  $filterInput
     */
    private function exportFilterSummary(array $filterInput): string
    {
        $categoryKey = (string) ($filterInput['category'] ?? '');
        $filters = is_array($filterInput['filters'] ?? null) ? $filterInput['filters'] : [];
        $parts = [$categoryKey];

        $definitions = [];

        try {
            $definitions = ReportSchema::category($categoryKey)['filters'] ?? [];
        } catch (\InvalidArgumentException) {
            $definitions = [];
        }

        foreach ($definitions as $filter) {
            if (! is_array($filter)) {
                continue;
            }

            $state = ReportSchema::filterState($filters, (string) $filter['key']);
            $piece = $filter['key'].':'.$state['mode'];

            if ($state['ids'] !== []) {
                $piece .= ':'.implode(',', $state['ids']);
            }

            if ($state['value'] !== null && $state['value'] !== '') {
                $piece .= '='.$state['value'];
            }

            $parts[] = $piece;
        }

        return mb_substr(implode(' ', $parts), 0, 45);
    }

    /**
     * @param  array<string, mixed>  $category
     * @return array<string, mixed>
     */
    private function publicCategory(string $key, array $category): array
    {
        $label = (string) $category['label'];
        $defaults = array_flip($category['default_columns'] ?? []);
        $columns = [];

        foreach (ReportSchema::columnsFor($key) as $column) {
            $columns[] = $this->publicColumn($column, isset($defaults[$column['key']]));
        }

        return [
            'key' => $key,
            'label' => $label,
            'level' => $category['level'],
            'questions' => ($category['questions'] ?? false) === true,
            'filters' => $this->publicFilters($category),
            'columns' => $columns,
        ];
    }

    /**
     * @param  array<string, mixed>  $category
     * @return list<array<string, mixed>>
     */
    private function publicFilters(array $category): array
    {
        $filters = [];

        foreach ($category['filters'] ?? [] as $filter) {
            if (! is_array($filter)) {
                continue;
            }

            $public = [
                'key' => $filter['key'],
                'label' => $filter['label'],
                'type' => $filter['type'],
                'owns' => array_values($filter['owns'] ?? []),
            ];

            if (($filter['type'] ?? '') === 'lookup') {
                $plural = $filter['plural'] ?? Str::plural((string) $filter['label']);
                $modes = [];

                foreach ($filter['modes'] ?? [] as $mode) {
                    $modes[] = [
                        'value' => $mode,
                        'label' => match ($mode) {
                            'all' => "All {$plural}",
                            'one' => "Per {$filter['label']}",
                            'multiple' => 'Select 1 or more',
                            default => (string) $mode,
                        },
                    ];
                }

                $public['modes'] = $modes;
            } else {
                $public['choices'] = array_map(function (array $choice): array {
                    $item = [
                        'value' => $choice['value'],
                        'label' => $choice['label'],
                        'numeric' => (bool) ($choice['numeric'] ?? false),
                        'singular' => (bool) ($choice['singular'] ?? false),
                    ];

                    if (isset($choice['min'])) {
                        $item['min'] = $choice['min'];
                    }

                    if (isset($choice['max'])) {
                        $item['max'] = $choice['max'];
                    }

                    return $item;
                }, $filter['choices'] ?? []);
            }

            $filters[] = $public;
        }

        return $filters;
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
        if ($this->isUnansweredQuestions($model, $column)) {
            return $this->unansweredPlaceholder($column);
        }

        $format = ReportSchema::format($column);
        $source = (string) ($column['source'] ?? '');
        $target = $source === '' ? $model : data_get($model, $source);

        if ($format === 'value' && $target === null && array_key_exists('null_as', $column)) {
            $target = $column['null_as'];
        }

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

    private function isUnansweredQuestions(Model $model, array $column): bool
    {
        $source = (string) ($column['source'] ?? '');

        if ($source !== 'questions' && ! str_starts_with($source, 'questions.')) {
            return false;
        }

        return data_get($model, 'questions') === null;
    }

    /**
     * @param  array<string, mixed>  $column
     */
    private function unansweredPlaceholder(array $column): mixed
    {
        if (ReportSchema::format($column) !== 'group') {
            return 'Not Answered';
        }

        $row = [];

        foreach ($column['columns'] ?? [] as $child) {
            if (is_array($child) && isset($child['key'])) {
                $row[$child['key']] = 'Not Answered';
            }
        }

        if (($column['source'] ?? '') === 'questions') {
            return $row;
        }

        return [$row];
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
