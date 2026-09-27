<?php

namespace App\Http\Requests\Report;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReportPreviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $filter = $this->input('filter');

        if (! is_array($filter)) {
            $filter = [];
        }

        $ids = $filter['ids'] ?? [];
        $filter['ids'] = is_array($ids) ? array_values($ids) : [];

        $merge = ['filter' => $filter];

        if (is_string($this->input('title'))) {
            $merge['title'] = trim($this->input('title'));
        }

        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        [$table, $key] = $this->filterTable();
        $restrictIds = $table !== '' && $this->input('filter.mode') !== 'all';

        return [
            'category' => ['required', 'string', Rule::in(array_keys(config('report_categories', [])))],
            'filter' => ['required', 'array'],
            'filter.mode' => ['required', 'string', Rule::in(['all', 'one', 'multiple'])],
            'filter.ids' => [
                'present',
                'array',
                Rule::when($this->input('filter.mode') === 'all', ['max:0']),
                Rule::when($this->input('filter.mode') === 'one', ['size:1']),
                Rule::when($this->input('filter.mode') === 'multiple', ['min:1', 'max:50']),
            ],
            'filter.ids.*' => array_values(array_filter([
                'integer',
                'distinct',
                $restrictIds ? Rule::exists($table, $key) : null,
            ])),
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
                'max:120',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! is_string($value) || preg_match('/[\p{L}\p{N}]/u', $value) !== 1) {
                        $fail('Report title must include letters or numbers.');
                    }
                },
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

                $allowed = array_column(
                    config('report_columns.'.$category['level'], []),
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
            'filter.mode.in' => 'Choose all, one, or more than one.',
            'filter.ids.max' => 'The all option does not take a specific selection.',
            'filter.ids.size' => 'Select one record for this report.',
            'filter.ids.min' => 'Select at least one record for this report.',
            'filter.ids.*.exists' => 'One of the selected records does not exist.',
            'selected_columns.required' => 'Select at least one column.',
            'selected_columns.min' => 'Select at least one column.',
            'title.required' => 'Report title is required.',
            'title.max' => 'Report title may not be longer than 120 characters.',
            'format.in' => 'Choose PDF or Excel.',
        ];
    }

    public function isExport(): bool
    {
        return $this->routeIs('reports.export');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function filterTable(): array
    {
        $categoryKey = $this->input('category');
        $categories = config('report_categories', []);

        if (! is_string($categoryKey) || ! isset($categories[$categoryKey]['filter_model'])) {
            return ['', ''];
        }

        $modelClass = $categories[$categoryKey]['filter_model'];
        $model = new $modelClass;

        return [$model->getTable(), $model->getKeyName()];
    }
}
