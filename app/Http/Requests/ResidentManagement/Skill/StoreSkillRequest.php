<?php

namespace App\Http\Requests\ResidentManagement\Skill;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSkillRequest extends FormRequest
{
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased(['skills_development_training']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'skills_development_training' => ['required', 'string', 'max:45'],
            'skill_type_id' => ['required', 'integer', Rule::exists('skill_type', 'skill_type_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'skills_development_training.required' => 'Skills development training is required.',
            'skill_type_id.exists' => 'The selected skill type does not exist.',
        ];
    }
}
