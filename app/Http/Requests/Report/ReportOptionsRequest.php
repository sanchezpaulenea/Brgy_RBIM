<?php

namespace App\Http\Requests\Report;

use App\Services\ReportSchema;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReportOptionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('search'))) {
            $this->merge([
                'search' => trim($this->input('search')),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(array_keys(config('report_categories', [])))],
            'filter' => ['required', 'string'],
            'search' => ['nullable', 'string', 'max:100'],
            'all' => ['sometimes', 'boolean'],
            'mode' => ['nullable', 'string', 'max:32'],
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
                $filterKey = $this->input('filter');

                if (! is_array($category) || ! is_string($filterKey)) {
                    return;
                }

                try {
                    $filter = ReportSchema::filterDefinition($category, $filterKey);
                } catch (\InvalidArgumentException) {
                    $validator->errors()->add('filter', 'Choose a filter from the list.');

                    return;
                }

                if (($filter['type'] ?? '') !== 'lookup') {
                    $validator->errors()->add('filter', 'This filter does not have a search list.');

                    return;
                }

                $mode = $this->input('mode');

                if (! is_string($mode) || $mode === '') {
                    return;
                }

                $modes = $filter['modes'] ?? ['all', 'one', 'multiple'];

                if (! in_array($mode, $modes, true)) {
                    $validator->errors()->add('mode', 'Choose a filter option from the list.');
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
            'filter.required' => 'Choose a filter from the list.',
        ];
    }
}
