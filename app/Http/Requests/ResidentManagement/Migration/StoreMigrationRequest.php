<?php

namespace App\Http\Requests\ResidentManagement\Migration;

use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Rules\ValidPlaceName;
use App\Services\ResidentManagement\Migration\MigrationClassifier;
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

        if ($this->exists('date_of_transfer_in_brgy')) {
            $this->merge([
                'date_of_transfer_in_brgy' => MigrationClassifier::normalizeTransferDate(
                    $this->input('date_of_transfer_in_brgy'),
                ),
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'previous_residence_6mos_brgy' => ['required', 'string', 'max:45', new ValidPlaceName('Previous residence (6 months) barangay')],
            'previous_residence_6mos_city_municipality' => [
                'required',
                'string',
                'max:45',
                new ValidPlaceName('Previous residence (6 months) city / municipality'),
            ],
            'previous_residence_5yrs_brgy' => ['required', 'string', 'max:45', new ValidPlaceName('Previous residence (5 years) barangay')],
            'previous_residence_5yrs_city_municipality' => [
                'required',
                'string',
                'max:45',
                new ValidPlaceName('Previous residence (5 years) city / municipality'),
            ],
            'date_of_transfer_in_brgy' => ['nullable', 'date', 'before_or_equal:today'],
            'reason_for_leaving_id' => ['nullable', 'integer', Rule::exists('reason_for_leaving', 'reason_for_leaving_id')],
            'will_return_to_previous_residence' => ['nullable', 'boolean'],
            'reason_for_transfer_id' => ['nullable', 'integer', Rule::exists('reason_for_transfer', 'reason_for_transfer_id')],
            'duration_of_stay' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'previous_residence_6mos_brgy.required' => 'Previous residence (6 months) barangay is required.',
            'previous_residence_6mos_city_municipality.required' => 'Previous residence (6 months) city / municipality is required.',
            'previous_residence_5yrs_brgy.required' => 'Previous residence (5 years) barangay is required.',
            'previous_residence_5yrs_city_municipality.required' => 'Previous residence (5 years) city / municipality is required.',
            'reason_for_leaving_id.exists' => 'The selected reason for leaving does not exist.',
            'reason_for_transfer_id.exists' => 'The selected reason for transfer does not exist.',
            'date_of_transfer_in_brgy.before_or_equal' => 'Date of transfer cannot be in the future.',
        ];
    }
}
