<?php

namespace App\Http\Requests\ResidentManagement\Health;

use App\Enums\LookupType;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use Illuminate\Foundation\Http\FormRequest;

class StoreHealthLookupRequest extends FormRequest
{
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $column = $this->lookupType()->labelColumn();

        if ($column !== null && $this->exists($column)) {
            $this->mergeTitleCased([$column]);
        }

        if ($this->exists('label')) {
            $this->mergeTitleCased(['label']);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $column = $this->lookupType()->labelColumn() ?? 'label';

        return [
            $column => ['required_without:label', 'nullable', 'string', 'max:45'],
            'label' => ['required_without:'.$column, 'nullable', 'string', 'max:45'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $label = str_replace('-', ' ', $this->lookupType()->value);

        return [
            $this->lookupType()->labelColumn().'.required_without' => ucfirst($label).' is required.',
            'label.required_without' => ucfirst($label).' is required.',
        ];
    }

    private function lookupType(): LookupType
    {
        $slug = (string) ($this->route('lookup') ?? $this->route()->defaults['lookup'] ?? '');

        return LookupType::fromSlug($slug);
    }
}
