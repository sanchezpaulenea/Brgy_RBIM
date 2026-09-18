<?php

namespace App\Http\Requests\HouseholdManagement;

use App\Http\Requests\HouseholdManagement\Concerns\NormalizesHouseholdAddress;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateHouseholdRequest extends FormRequest
{
    use NormalizesHouseholdAddress;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->mergeNormalizedAddressFields([
            'house_lot',
        ]);
        $this->mergeBasementLevelFromAnswer();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'clan_id' => ['sometimes', 'integer', Rule::exists('clan', 'clan_id')],
            'street_id' => ['sometimes', 'integer', Rule::exists('street', 'street_id')],
            'house_lot' => ['sometimes', 'nullable', 'string', 'max:45'],
            'number_of_house_story' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
            'has_basement' => ['sometimes', 'required', 'boolean'],
            'number_of_basement_level' => [
                'sometimes',
                Rule::requiredIf(fn () => $this->exists('has_basement') && $this->boolean('has_basement')),
                'nullable',
                'integer',
                'min:0',
                'max:20',
            ],
            'household_status_id' => [
                'sometimes',
                'integer',
                Rule::exists('household_status', 'household_status_id'),
            ],
            'head_resident_id' => ['prohibited'],
            'head' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clan_id.exists' => 'The selected clan does not exist.',
            'street_id.exists' => 'The selected street does not exist.',
            'household_status_id.exists' => 'The selected household status does not exist.',
            'number_of_house_story.min' => 'Number of house stories must be at least 1.',
            'has_basement.required' => 'Please indicate whether the house has a basement.',
            'number_of_basement_level.required' => 'Number of basement levels is required when the house has a basement.',
            'head_resident_id.prohibited' => 'The household head cannot be changed on this update.',
            'head.prohibited' => 'The household head cannot be changed on this update.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $updatable = [
                    'clan_id',
                    'street_id',
                    'house_lot',
                    'number_of_house_story',
                    'has_basement',
                    'number_of_basement_level',
                    'household_status_id',
                ];

                foreach ($updatable as $field) {
                    if ($this->exists($field)) {
                        return;
                    }
                }

                $validator->errors()->add(
                    'household',
                    'Provide at least one household field to update.',
                );
            },
            function (Validator $validator): void {
                if (! $this->exists('has_basement') || ! $this->boolean('has_basement')) {
                    return;
                }

                $levels = $this->input('number_of_basement_level');

                if (! is_numeric($levels) || (int) $levels < 1) {
                    $validator->errors()->add(
                        'number_of_basement_level',
                        'Number of basement levels is required when the house has a basement.',
                    );
                }
            },
        ];
    }
}
