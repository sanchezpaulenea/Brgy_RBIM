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
        $this->mergeTitleCased(['skills_development_training', 'skill_type']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'skills_development_training' => ['required', 'string', 'max:45'],
            'skill_type_id' => ['required_without:skill_type', 'nullable', 'integer', Rule::exists('skill_type', 'skill_type_id')],
            'skill_type' => ['required_without:skill_type_id', 'nullable', 'string', 'max:45'],
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
            'skill_type_id.required_without' => 'Skill type is required.',
            'skill_type.required_without' => 'Skill type is required.',
        ];
    }
}
