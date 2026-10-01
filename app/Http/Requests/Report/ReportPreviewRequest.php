<?php

namespace App\Http\Requests\Report;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Services\ReportSchema;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReportPreviewRequest extends FormRequest
{
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $filters = $this->input('filters');
        $normalized = [];

        if (is_array($filters)) {
            foreach ($filters as $key => $filter) {
                if (! is_string($key) || ! is_array($filter)) {
                    continue;
                }

                $ids = $filter['ids'] ?? [];
                $normalized[$key] = [
                    'mode' => $filter['mode'] ?? null,
                    'ids' => is_array($ids) ? array_values($ids) : [],
                    'value' => $filter['value'] ?? null,
                    'min' => $filter['min'] ?? null,
                    'max' => $filter['max'] ?? null,
                ];
            }
        }

        $this->merge(['filters' => $normalized]);
        $this->mergeTitleCased(['title', 'subtitle']);
    }

    /**
     * Preserve words that are already ALL CAPS (CTC, PWD, SK, NCSC).
     */
    protected function titleCaseValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $squished = Str::of($value)->squish()->toString();

        if ($squished === '') {
            return null;
        }

        $formatted = preg_replace_callback(
            '/\S+/u',
            function (array $match): string {
                $word = $match[0];
                $letters = preg_replace('/[^\p{L}]/u', '', $word) ?? '';

                if ($letters !== '' && preg_match('/^\p{Lu}+$/u', $letters) === 1) {
                    return $word;
                }

                return Str::title(Str::lower($word));
            },
            $squished,
        );

        return is_string($formatted) && $formatted !== '' ? $formatted : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(array_keys(config('report_categories', [])))],
            'filters' => ['required', 'array'],
            'selected_columns' => ['required', 'array', 'min:1', 'max:50'],
            'selected_columns.*' => ['required', 'string', 'distinct'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'format' => [
                Rule::excludeIf(fn (): bool => ! $this->isExport()),
                'required',
                'string',
                Rule::in(['pdf', 'excel']),
            ],
            'title' => [
                Rule::excludeIf(fn (): bool => ! $this->isExport()),
                'required',
                'string',
                'max:100',
                $this->requiresLetterOrNumber('Report title'),
            ],
            'subtitle' => [
                Rule::excludeIf(fn (): bool => ! $this->isExport()),
                'nullable',
                'string',
                'max:100',
                $this->requiresLetterOrNumber('Report subtitle'),
            ],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $category = config('report_categories.'.$this->input('category'));

                if (! is_array($category)) {
                    return;
                }

                $this->validateFilters($validator, $category);
                $this->validateColumns($validator);
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category.in' => 'Choose a report category from the list.',
            'selected_columns.required' => 'Select at least one column.',
            'selected_columns.min' => 'Select at least one column.',
            'title.required' => 'Report title is required.',
            'title.max' => 'Report title may not be longer than 100 characters.',
            'subtitle.max' => 'Report subtitle may not be longer than 100 characters.',
            'format.in' => 'Choose PDF or Excel.',
        ];
    }

    public function isExport(): bool
    {
        return $this->routeIs('reports.export');
    }

    /**
     * @param  array<string, mixed>  $category
     */
    private function validateFilters(Validator $validator, array $category): void
    {
        $input = $this->input('filters', []);
        $known = [];

        foreach ($category['filters'] ?? [] as $filter) {
            if (! is_array($filter)) {
                continue;
            }

            $key = (string) $filter['key'];
            $known[$key] = true;

            if (! ReportSchema::filterApplies($category, $filter, $input)) {
                continue;
            }

            $state = is_array($input[$key] ?? null) ? $input[$key] : null;

            if ($state === null) {
                $validator->errors()->add("filters.{$key}", 'Choose a value for '.$filter['label'].'.');

                continue;
            }

            if (($filter['type'] ?? '') === 'lookup') {
                $this->validateLookup($validator, $filter, $state);

                continue;
            }

            if (($filter['type'] ?? '') === 'range') {
                $this->validateRange($validator, $filter, $state);

                continue;
            }

            $this->validateChoice($validator, $filter, $state);
        }

        foreach ($input as $key => $value) {
            if (! isset($known[$key])) {
                $validator->errors()->add('filters.'.$key, 'This filter is not part of the selected report.');
            }
        }
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array<string, mixed>  $state
     */
    private function validateLookup(Validator $validator, array $filter, array $state): void
    {
        $key = (string) $filter['key'];
        $modes = $filter['modes'] ?? ['all', 'one', 'multiple'];
        $mode = $state['mode'] ?? '';

        if (! in_array($mode, $modes, true)) {
            $validator->errors()->add("filters.{$key}.mode", 'Choose a filter option from the list.');

            return;
        }

        $ids = is_array($state['ids'] ?? null) ? $state['ids'] : [];

        if ($mode === 'all' && $ids !== []) {
            $validator->errors()->add("filters.{$key}.ids", 'The all option does not take a specific selection.');

            return;
        }

        if ($mode === 'one' && count($ids) !== 1) {
            $validator->errors()->add("filters.{$key}.ids", 'Select one record for this report.');

            return;
        }

        if ($mode === 'multiple' && (count($ids) < 1 || count($ids) > 100)) {
            $validator->errors()->add("filters.{$key}.ids", 'Select at least one record for this report.');

            return;
        }

        $parsed = [];

        foreach ($ids as $id) {
            if (! is_numeric($id) || (int) $id != $id) {
                $validator->errors()->add("filters.{$key}.ids", 'Select a record from the list.');

                return;
            }

            $parsed[] = (int) $id;
        }

        if (count($parsed) !== count(array_unique($parsed))) {
            $validator->errors()->add("filters.{$key}.ids", 'Select each record only once.');

            return;
        }

        if ($parsed === []) {
            return;
        }

        $modelClass = $filter['model'];
        $found = $modelClass::query()
            ->whereIn((string) $filter['id_column'], $parsed)
            ->pluck((string) $filter['id_column'])
            ->map(fn (mixed $id): int => (int) $id)
            ->all();

        if (count($found) !== count($parsed)) {
            $validator->errors()->add("filters.{$key}.ids", 'One of the selected records does not exist.');
        }
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array<string, mixed>  $state
     */
    private function validateChoice(Validator $validator, array $filter, array $state): void
    {
        $key = (string) $filter['key'];
        $mode = $state['mode'] ?? '';
        $matched = null;

        foreach ($filter['choices'] ?? [] as $choice) {
            if (is_array($choice) && ($choice['value'] ?? null) === $mode) {
                $matched = $choice;
            }
        }

        if ($matched === null) {
            $validator->errors()->add("filters.{$key}.mode", 'Choose a filter option from the list.');

            return;
        }

        if (($matched['numeric'] ?? false) !== true) {
            return;
        }

        $value = $state['value'] ?? null;
        $min = (int) ($matched['min'] ?? 0);
        $max = (int) ($matched['max'] ?? 100);

        if (! is_numeric($value) || (int) $value < $min || (int) $value > $max) {
            $validator->errors()->add(
                "filters.{$key}.value",
                'Enter a number from '.$min.' to '.$max.'.',
            );
        }
    }

    /**
     * @param  array<string, mixed>  $filter
     * @param  array<string, mixed>  $state
     */
    private function validateRange(Validator $validator, array $filter, array $state): void
    {
        $key = (string) $filter['key'];
        $scale = (int) ($filter['scale'] ?? 2);
        $minBound = (string) ($filter['min_bound'] ?? '0');
        $maxBound = (string) ($filter['max_bound'] ?? '99999999.99');
        $min = $this->decimalBound($state['min'] ?? null, $scale, $minBound, $maxBound);
        $max = $this->decimalBound($state['max'] ?? null, $scale, $minBound, $maxBound);

        if ($min === false) {
            $validator->errors()->add(
                "filters.{$key}.min",
                'Enter a minimum from '.$minBound.' to '.$maxBound.' with up to '.$scale.' decimal places.',
            );
        }

        if ($max === false) {
            $validator->errors()->add(
                "filters.{$key}.max",
                'Enter a maximum from '.$minBound.' to '.$maxBound.' with up to '.$scale.' decimal places.',
            );
        }

        if ($min === false || $max === false || $min === null || $max === null) {
            return;
        }

        if (bccomp($min, $max, $scale) === 1) {
            $validator->errors()->add("filters.{$key}.min", 'Minimum cannot be greater than maximum.');
        }
    }

    private function decimalBound(mixed $value, int $scale, string $minBound, string $maxBound): string|false|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (! is_string($value) || preg_match('/^(?:0|[1-9]\d*)(?:\.\d{1,'.$scale.'})?$/', $value) !== 1) {
            return false;
        }

        $normalized = bcadd($value, '0', $scale);

        if (bccomp($normalized, bcadd($minBound, '0', $scale), $scale) === -1) {
            return false;
        }

        if (bccomp($normalized, bcadd($maxBound, '0', $scale), $scale) === 1) {
            return false;
        }

        return $normalized;
    }

    private function validateColumns(Validator $validator): void
    {
        $allowed = array_column(
            ReportSchema::columnsFor((string) $this->input('category')),
            'key',
        );

        foreach ($this->input('selected_columns', []) as $index => $column) {
            if (! in_array($column, $allowed, true)) {
                $validator->errors()->add(
                    "selected_columns.{$index}",
                    'This column is not available for the selected report.',
                );
            }
        }

        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $presented = ReportSchema::presentColumns(
            (string) $this->input('category'),
            $this->input('selected_columns', []),
            $this->input('filters', []),
        );

        if ($presented === []) {
            $validator->errors()->add('selected_columns', 'Select at least one column.');
        }
    }

    private function requiresLetterOrNumber(string $label): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($label): void {
            if ($value === null || $value === '') {
                return;
            }

            if (! is_string($value) || preg_match('/[\p{L}\p{N}]/u', $value) !== 1) {
                $fail("{$label} must include letters or numbers.");
            }
        };
    }
}
