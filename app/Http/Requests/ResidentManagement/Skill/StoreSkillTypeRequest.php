<?php

namespace App\Http\Requests\ResidentManagement\Skill;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use Illuminate\Foundation\Http\FormRequest;

class StoreSkillTypeRequest extends FormRequest
{
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased(['skill_type', 'label']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'skill_type' => ['required_without:label', 'nullable', 'string', 'max:45'],
            'label' => ['required_without:skill_type', 'nullable', 'string', 'max:45'],
        ];
    }
}
