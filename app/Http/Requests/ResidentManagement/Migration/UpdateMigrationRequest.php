<?php

namespace App\Http\Requests\ResidentManagement\Migration;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Http\Requests\Concerns\TitleCasesAttributes;
use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMigrationRequest extends FormRequest
{
    use RequiresAtLeastOneField;
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
            'previous_residence_6mos_brgy' => [
                'sometimes',
                'nullable',
                'string',
                'max:45',
                new ValidPlaceName('Previous residence (6 months) barangay'),
            ],
            'previous_residence_6mos_city_municipality' => [
                'sometimes',
                'nullable',
                'string',
                'max:45',
                new ValidPlaceName('Previous residence (6 months) city / municipality'),
            ],
            'previous_residence_5yrs_brgy' => [
                'sometimes',
                'nullable',
                'string',
                'max:45',
                new ValidPlaceName('Previous residence (5 years) barangay'),
            ],
            'previous_residence_5yrs_city_municipality' => [
                'sometimes',
                'nullable',
                'string',
                'max:45',
                new ValidPlaceName('Previous residence (5 years) city / municipality'),
            ],
            'date_of_transfer_in_brgy' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'reason_for_leaving_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('reason_for_leaving', 'reason_for_leaving_id'),
            ],
            'will_return_to_previous_residence' => ['sometimes', 'required', 'boolean'],
            'reason_for_transfer_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('reason_for_transfer', 'reason_for_transfer_id'),
            ],
            'duration_of_stay' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'previous_residence_6mos_brgy',
                    'previous_residence_6mos_city_municipality',
                    'previous_residence_5yrs_brgy',
                    'previous_residence_5yrs_city_municipality',
                    'date_of_transfer_in_brgy',
                    'reason_for_leaving_id',
                    'will_return_to_previous_residence',
                    'reason_for_transfer_id',
                    'duration_of_stay',
                ], 'migration', 'Provide at least one migration field to update.');
            },
        ];
    }
}
