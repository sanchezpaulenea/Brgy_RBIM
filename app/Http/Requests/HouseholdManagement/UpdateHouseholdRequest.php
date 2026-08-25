<?php

namespace App\Http\Requests\HouseholdManagement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['house_lot', 'block_num', 'building_name', 'unit_num'] as $field) {
            if ($this->exists($field)) {
                $merge[$field] = $this->normalizeOptionalText($this->input($field));
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
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
        ];
    }

    private function normalizeOptionalText(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $formatted = Str::of($value)->squish()->toString();

        return $formatted === '' ? null : $formatted;
    }
}
