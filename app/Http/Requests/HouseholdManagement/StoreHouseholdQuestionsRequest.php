<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\Concerns\ValidatesHouseholdQuestions;
use Illuminate\Foundation\Http\FormRequest;

class StoreHouseholdQuestionsRequest extends FormRequest
{
    use ValidatesHouseholdQuestions;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareHouseholdQuestionInput();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->householdQuestionRules(true);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->householdQuestionMessages();
    }
}
