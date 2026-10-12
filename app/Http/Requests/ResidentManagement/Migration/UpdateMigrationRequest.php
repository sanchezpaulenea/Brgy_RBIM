<?php

namespace App\Http\Requests\ResidentManagement\Migration;

use App\Http\Requests\Concerns\RequiresAtLeastOneField;
use App\Services\ResidentManagement\Migration\MigrationClassifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMigrationRequest extends FormRequest
{
    use RequiresAtLeastOneField;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('date_of_transfer_in_brgy')) {
            $this->merge([
                'date_of_transfer_in_brgy' => MigrationClassifier::normalizeTransferDate(
                    $this->input('date_of_transfer_in_brgy'),
                ),
            ]);
        }

        if ($this->exists('duration_of_stay')) {
            $this->merge([
                'duration_of_stay' => MigrationClassifier::normalizeTransferDate(
                    $this->input('duration_of_stay'),
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
            'since_birth' => ['sometimes', 'boolean'],
            'date_of_transfer_in_brgy' => ['sometimes', 'nullable', 'date', 'before_or_equal:today'],
            'reason_for_leaving_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('reason_for_leaving', 'reason_for_leaving_id'),
            ],
            'will_return_to_previous_residence' => ['sometimes', 'nullable', 'boolean'],
            'reason_for_transfer_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('reason_for_transfer', 'reason_for_transfer_id'),
            ],
            'duration_of_stay' => ['sometimes', 'nullable', 'date'],
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
            'date_of_transfer_in_brgy.before_or_equal' => 'Date of transfer cannot be in the future.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->requireAtLeastOne($validator, [
                    'since_birth',
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
