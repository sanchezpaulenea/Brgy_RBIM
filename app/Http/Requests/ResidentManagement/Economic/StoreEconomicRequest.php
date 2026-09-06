<?php

namespace App\Http\Requests\ResidentManagement\Economic;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEconomicRequest extends FormRequest
{
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased(['place_of_work_business']);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'monthly_income' => ['required', 'integer', 'min:0'],
            'source_of_income_id' => ['required', 'integer', Rule::exists('source_of_income', 'source_of_income_id')],
            'status_of_work_business_id' => [
                'required',
                'integer',
                Rule::exists('status_of_work_business', 'status_of_work_business_id'),
            ],
            'place_of_work_business' => ['nullable', 'string', 'max:45', new ValidPlaceName('Place of work / business')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'monthly_income.required' => 'Monthly income is required.',
            'monthly_income.integer' => 'Monthly income must be numeric.',
            'source_of_income_id.exists' => 'The selected source of income does not exist.',
            'status_of_work_business_id.exists' => 'The selected work / business status does not exist.',
        ];
    }
}
