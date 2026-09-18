<?php

namespace App\Http\Requests\ResidentManagement\Economic;

use App\Http\Requests\Concerns\NormalizesMonthlyIncome;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Models\ResidentManagement\Economic\Economic;
use App\Models\ResidentManagement\Economic\SourceOfIncome;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEconomicRequest extends FormRequest
{
    use NormalizesMonthlyIncome;
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased(['place_of_work_business']);
        $this->mergeNormalizedMonthlyIncome();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'monthly_income' => [
                'required',
                'numeric',
                'min:0',
                'max:'.Economic::MAX_MONTHLY_INCOME,
                'decimal:0,'.Economic::MONTHLY_INCOME_SCALE,
            ],
            'source_of_income_id' => ['required', 'integer', Rule::exists('source_of_income', 'source_of_income_id')],
            'status_of_work_business_id' => [
                Rule::excludeIf(fn () => $this->skipsWorkDetails()),
                'required',
                'integer',
                Rule::exists('status_of_work_business', 'status_of_work_business_id'),
            ],
            'place_of_work_business' => [
                Rule::excludeIf(fn () => $this->skipsWorkDetails()),
                'required',
                'string',
                'max:45',
                new ValidPlaceName('Place of work / business'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'monthly_income.required' => 'Monthly income is required.',
            'monthly_income.numeric' => 'Monthly income must be an amount, for example 12500.00.',
            'monthly_income.decimal' => 'Monthly income may have at most two decimal places.',
            'monthly_income.max' => 'Monthly income may not be greater than '.Economic::MAX_MONTHLY_INCOME.'.',
            'source_of_income_id.exists' => 'The selected source of income does not exist.',
            'status_of_work_business_id.exists' => 'The selected work / business status does not exist.',
            'status_of_work_business_id.required' => 'Status of work / business is required.',
            'place_of_work_business.required' => 'Place of work / business is required.',
        ];
    }

    private function skipsWorkDetails(): bool
    {
        return SourceOfIncome::skipsWorkDetails($this->input('source_of_income_id'));
    }
}
