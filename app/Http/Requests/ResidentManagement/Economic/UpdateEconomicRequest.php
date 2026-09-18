<?php

namespace App\Http\Requests\ResidentManagement\Economic;

use App\Http\Requests\Concerns\NormalizesMonthlyIncome;
use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Models\ResidentManagement\Economic\Economic;
use App\Models\ResidentManagement\Economic\SourceOfIncome;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEconomicRequest extends FormRequest
{
    use NormalizesMonthlyIncome;
    use RequiresAtLeastOneField;
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
                'sometimes',
                'required',
                'numeric',
                'min:0',
                'max:'.Economic::MAX_MONTHLY_INCOME,
                'decimal:0,'.Economic::MONTHLY_INCOME_SCALE,
            ],
            'source_of_income_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('source_of_income', 'source_of_income_id'),
            ],
            'status_of_work_business_id' => [
                Rule::excludeIf(fn () => $this->skipsWorkDetails()),
                'sometimes',
                'required',
                'integer',
                Rule::exists('status_of_work_business', 'status_of_work_business_id'),
            ],
            'place_of_work_business' => [
                Rule::excludeIf(fn () => $this->skipsWorkDetails()),
                'sometimes',
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
            'monthly_income.numeric' => 'Monthly income must be an amount, for example 12500.00.',
            'monthly_income.decimal' => 'Monthly income may have at most two decimal places.',
            'monthly_income.max' => 'Monthly income may not be greater than '.Economic::MAX_MONTHLY_INCOME.'.',
            'status_of_work_business_id.exists' => 'The selected work / business status does not exist.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'monthly_income',
                    'source_of_income_id',
                    'status_of_work_business_id',
                    'place_of_work_business',
                ], 'economic', 'Provide at least one economic field to update.');
            },
        ];
    }

    private function skipsWorkDetails(): bool
    {
        $sourceId = $this->input('source_of_income_id')
            ?? $this->route('economic')?->source_of_income_id;

        return SourceOfIncome::skipsWorkDetails($sourceId);
    }
}
