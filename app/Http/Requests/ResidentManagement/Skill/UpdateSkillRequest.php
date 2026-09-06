<?php

namespace App\Http\Requests\ResidentManagement\Skill;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSkillRequest extends FormRequest
{
    use RequiresAtLeastOneField;
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
            'skills_development_training' => ['sometimes', 'required', 'string', 'max:45'],
            'skill_type_id' => ['sometimes', 'required', 'integer', Rule::exists('skill_type', 'skill_type_id')],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne(
                    $validator,
                    ['skills_development_training', 'skill_type_id'],
                    'skills',
                    'Provide at least one skills development field to update.',
                );
            },
        ];
    }
}
