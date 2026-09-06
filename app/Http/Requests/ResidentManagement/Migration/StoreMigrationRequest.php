<?php

namespace App\Http\Requests\ResidentManagement\Migration;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMigrationRequest extends FormRequest
{
    use TitleCasesAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeTitleCased([
            'previous_residence_6mos_brgy',
            'previous_residence_6mos_city_municipality',
            'previous_residence_5yrs_brgy',
            'previous_residence_5yrs_city_municipality',
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'previous_residence_6mos_brgy' => ['nullable', 'string', 'max:45', new ValidPlaceName('Previous residence (6 months) barangay')],
            'previous_residence_6mos_city_municipality' => [
                'nullable',
                'string',
                'max:45',
                new ValidPlaceName('Previous residence (6 months) city / municipality'),
            ],
            'previous_residence_5yrs_brgy' => ['nullable', 'string', 'max:45', new ValidPlaceName('Previous residence (5 years) barangay')],
            'previous_residence_5yrs_city_municipality' => [
                'nullable',
                'string',
                'max:45',
                new ValidPlaceName('Previous residence (5 years) city / municipality'),
            ],
            'date_of_transfer_in_brgy' => ['nullable', 'date', 'before_or_equal:today'],
            'reason_for_leaving_id' => ['required', 'integer', Rule::exists('reason_for_leaving', 'reason_for_leaving_id')],
            'will_return_to_previous_residence' => ['required', 'boolean'],
            'reason_for_transfer_id' => ['required', 'integer', Rule::exists('reason_for_transfer', 'reason_for_transfer_id')],
            'duration_of_stay' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason_for_leaving_id.exists' => 'The selected reason for leaving does not exist.',
            'reason_for_transfer_id.exists' => 'The selected reason for transfer does not exist.',
        ];
    }
}
