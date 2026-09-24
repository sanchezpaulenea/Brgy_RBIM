<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Enums\LookupType;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use Illuminate\Foundation\Http\FormRequest;

class StorePetLookupRequest extends FormRequest
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
        $max = $this->lookupType() === LookupType::Sex ? 10 : 45;

        return [
            $column => ['required_without:label', 'nullable', 'string', 'max:'.$max],
            'label' => ['required_without:'.$column, 'nullable', 'string', 'max:'.$max],
        ];
    }

    private function lookupType(): LookupType
    {
        $slug = (string) ($this->route('lookup') ?? $this->route()->defaults['lookup'] ?? '');

        return LookupType::fromSlug($slug);
    }
}
