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
            'block_num',
            'building_name',
            'unit_num',
        ]);
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
            'block_num' => ['sometimes', 'nullable', 'string', 'max:45'],
            'building_name' => ['sometimes', 'nullable', 'string', 'max:45'],
            'unit_num' => ['sometimes', 'nullable', 'string', 'max:45'],
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
                    'block_num',
                    'building_name',
                    'unit_num',
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
                $this->validateUniqueLotAndBlock($validator, $this->householdFromRoute());
            },
        ];
    }
}
