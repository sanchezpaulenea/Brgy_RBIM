<?php

namespace App\Http\Requests\Certificate\Concerns;

use App\Models\ResidentManagement\Demographic\Resident;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesCertificateInput
{
    protected function prepareCertificateInput(): void
    {
        $merge = [];

        if ($this->exists('purpose_of_request') && is_string($this->input('purpose_of_request'))) {
            $merge['purpose_of_request'] = trim($this->input('purpose_of_request'));
        }

        foreach ([
            'representative_name',
            'representative_relationship',
            'place_of_death',
            'cause_of_death',
            'ethnic_group',
            'stated_civil_status',
            'solo_parent_circumstance',
            'solo_parent_president_name',
            'solo_parent_president_address',
            'solo_parent_neighbor_name',
            'solo_parent_neighbor_address',
            'solo_parent_kagawad_name',
        ] as $field) {
            if (! $this->exists($field) || ! is_string($this->input($field))) {
                continue;
            }

            $cleaned = trim((string) preg_replace('/[\x00-\x1F\x7F]/', '', $this->input($field)));
            $merge[$field] = $cleaned === '' ? null : $cleaned;
        }

        if ($this->exists('stated_income_amount') && $this->input('stated_income_amount') === '') {
            $merge['stated_income_amount'] = null;
        }

        if ($this->exists('date_of_death') && $this->input('date_of_death') === '') {
            $merge['date_of_death'] = null;
        }

        if ($this->exists('resident_since_year') && $this->input('resident_since_year') === '') {
            $merge['resident_since_year'] = null;
        }

        if ($this->exists('children') && is_array($this->input('children'))) {
            $children = [];

            foreach ($this->input('children') as $child) {
                if (! is_array($child)) {
                    continue;
                }

                $name = is_string($child['child_name'] ?? null)
                    ? trim((string) preg_replace('/[\x00-\x1F\x7F]/', '', $child['child_name']))
                    : '';
                $born = $child['date_of_birth'] ?? null;
                $born = $born === '' ? null : $born;

                if ($name === '' && $born === null) {
                    continue;
                }

                $children[] = [
                    'child_name' => $name === '' ? null : $name,
                    'date_of_birth' => $born,
                ];
            }

            $merge['children'] = $children;
        }

        if ($this->exists('overrides') && is_array($this->input('overrides'))) {
            $overrides = [];

            foreach (['resident_name', 'address', 'requested_by'] as $field) {
                if (! array_key_exists($field, $this->input('overrides'))) {
                    continue;
                }

                $value = $this->input('overrides')[$field];

                if (! is_string($value)) {
                    $overrides[$field] = $value;

                    continue;
                }

                $cleaned = trim((string) preg_replace('/[\x00-\x1F\x7F]/', '', $value));
                $overrides[$field] = $cleaned === '' ? null : $cleaned;
            }

            $merge['overrides'] = $overrides;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function certificateRules(bool $withSignature = false, bool $strictTypeFields = true): array
    {
        $rules = [
            'issuer_id' => ['prohibited'],
            'punong_barangay_name' => ['prohibited'],
            'punong_barangay_signature' => ['prohibited'],
            'type_of_certificate_id' => [
                'required',
                'integer',
                Rule::exists('type_of_certificate', 'type_of_certificate_id'),
            ],
            'resident_id' => [
                'required',
                'integer',
                Rule::exists('resident', 'resident_id'),
            ],
            'purpose_of_request' => [
                'required',
                'string',
                'min:3',
                'max:255',
                $this->purposeRule(),
            ],
            'representative_requested' => ['sometimes', 'boolean'],
            'representative_name' => [
                Rule::requiredIf(fn (): bool => $strictTypeFields && $this->boolean('representative_requested')),
                'nullable',
                'string',
                'max:100',
                $this->personTextRule('Representative name'),
            ],
            'representative_relationship' => [
                Rule::requiredIf(fn (): bool => $strictTypeFields && $this->boolean('representative_requested')),
                'nullable',
                'string',
                'max:45',
                $this->personTextRule('Relationship'),
            ],
            'date_of_death' => [
                Rule::requiredIf(fn (): bool => $strictTypeFields && $this->certificateKind() === 'death'),
                Rule::prohibitedIf(fn (): bool => $this->filled('date_of_death') && $this->certificateKind() !== 'death'),
                'nullable',
                'date',
                'before_or_equal:today',
            ],
            'include_income_amount' => ['sometimes', 'boolean'],
            'stated_income_amount' => [
                Rule::requiredIf(fn (): bool => $strictTypeFields && $this->needsIncomeAmount()),
                'nullable',
                'numeric',
                'gt:0',
                'decimal:0,2',
                'max:99999999.99',
            ],
            'place_of_death' => [
                Rule::prohibitedIf(fn (): bool => $this->filled('place_of_death') && $this->certificateKind() !== 'death'),
                'nullable',
                'string',
                'max:100',
                $this->personTextRule('Place of death'),
            ],
            'cause_of_death' => [
                Rule::prohibitedIf(fn (): bool => $this->filled('cause_of_death') && $this->certificateKind() !== 'death'),
                'nullable',
                'string',
                'max:100',
                $this->personTextRule('Cause of death'),
            ],
            'ethnic_group' => [
                Rule::requiredIf(fn (): bool => $strictTypeFields && $this->certificateKind() === 'death'),
                Rule::prohibitedIf(fn (): bool => $this->filled('ethnic_group') && $this->certificateKind() !== 'death'),
                'nullable',
                'string',
                'max:60',
                $this->personTextRule('Ethnic group'),
            ],
            'resident_since_year' => [
                Rule::requiredIf(fn (): bool => $strictTypeFields && $this->needsResidencyYear()),
                Rule::prohibitedIf(fn (): bool => $this->filled('resident_since_year') && ! $this->needsResidencyYear()),
                'nullable',
                'integer',
                'between:1900,'.date('Y'),
            ],
            'stated_civil_status' => [
                Rule::requiredIf(fn (): bool => $strictTypeFields && $this->needsCivilStatus()),
                Rule::prohibitedIf(fn (): bool => $this->filled('stated_civil_status') && ! $this->needsCivilStatus()),
                'nullable',
                'string',
                'max:45',
                $this->personTextRule('Civil status'),
            ],
            'solo_parent_circumstance' => [
                Rule::requiredIf(fn (): bool => $strictTypeFields && $this->certificateKind() === 'solo_parent'),
                Rule::prohibitedIf(fn (): bool => $this->filled('solo_parent_circumstance') && $this->certificateKind() !== 'solo_parent'),
                'nullable',
                'string',
                'max:120',
                $this->personTextRule('Solo parent circumstance'),
            ],
            'solo_parent_president_name' => $this->soloPersonRules('solo_parent_president_name', 'Solo Parent President name', $strictTypeFields),
            'solo_parent_neighbor_name' => $this->soloPersonRules('solo_parent_neighbor_name', 'Neighbor name', $strictTypeFields),
            'solo_parent_kagawad_name' => $this->soloPersonRules('solo_parent_kagawad_name', 'Kagawad name', $strictTypeFields),
            'solo_parent_president_address' => $this->soloAddressRules('solo_parent_president_address', 'Solo Parent President address', $strictTypeFields),
            'solo_parent_neighbor_address' => $this->soloAddressRules('solo_parent_neighbor_address', 'Neighbor address', $strictTypeFields),
            'children' => [
                Rule::requiredIf(fn (): bool => $strictTypeFields && $this->certificateKind() === 'solo_parent'),
                Rule::prohibitedIf(fn (): bool => $this->filled('children') && $this->certificateKind() !== 'solo_parent'),
                'nullable',
                'array',
                'max:10',
                Rule::when(
                    fn (): bool => $strictTypeFields && $this->certificateKind() === 'solo_parent',
                    ['min:1'],
                ),
            ],
            'children.*.child_name' => [
                'required',
                'string',
                'max:100',
                $this->personTextRule('Child name'),
            ],
            'children.*.date_of_birth' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'overrides' => ['sometimes', 'array'],
            'overrides.resident_name' => ['sometimes', 'nullable', 'string', 'max:150', $this->letterRule('Resident name')],
            'overrides.address' => ['sometimes', 'nullable', 'string', 'max:150', $this->letterRule('Address')],
            'overrides.requested_by' => ['sometimes', 'nullable', 'string', 'max:150', $this->letterRule('Requester')],
        ];

        if ($withSignature) {
            $rules['attach_pb_signature'] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function certificateMessages(): array
    {
        return [
            'issuer_id.prohibited' => 'Issuer is assigned from the signed-in account and cannot be submitted.',
            'punong_barangay_name.prohibited' => 'Punong Barangay name is read from system settings and cannot be submitted.',
            'punong_barangay_signature.prohibited' => 'The Punong Barangay signature is read from system settings and cannot be submitted.',
            'type_of_certificate_id.required' => 'Certificate type is required.',
            'type_of_certificate_id.exists' => 'Select a certificate type.',
            'resident_id.required' => 'Requester is required.',
            'resident_id.exists' => 'Select a resident.',
            'purpose_of_request.required' => 'Purpose of request is required.',
            'purpose_of_request.min' => 'Purpose of request must be at least 3 characters.',
            'purpose_of_request.max' => 'Purpose of request must not exceed 255 characters.',
            'representative_name.required' => 'Representative name is required when the request is made by a representative.',
            'representative_name.max' => 'Representative name must not exceed 100 characters.',
            'representative_relationship.required' => 'Relationship to the certificate owner is required when the request is made by a representative.',
            'representative_relationship.max' => 'Relationship must not exceed 45 characters.',
            'date_of_death.required' => 'Date of death is required for a Death Certificate.',
            'date_of_death.date' => 'Date of death must be a valid date.',
            'date_of_death.before_or_equal' => 'Date of death cannot be in the future.',
            'date_of_death.prohibited' => 'Date of death is only collected for a Death Certificate.',
            'stated_income_amount.required' => 'The annual income threshold is required for Low Income Certification (with amount).',
            'stated_income_amount.numeric' => 'The annual income threshold must be a number.',
            'stated_income_amount.gt' => 'The annual income threshold must be greater than zero.',
            'stated_income_amount.decimal' => 'The annual income threshold may have at most 2 decimal places.',
            'stated_income_amount.max' => 'The annual income threshold is too large.',
            'place_of_death.required' => 'Place of death is required for a Death Certificate.',
            'cause_of_death.required' => 'Cause of death is required for a Death Certificate.',
            'ethnic_group.required' => 'Ethnic group is required for a Death Certificate.',
            'resident_since_year.required' => 'Resident since year is required when the low income certification has no stated amount.',
            'resident_since_year.between' => 'Resident since year must be from 1900 through the current year.',
            'stated_civil_status.required' => 'Civil status is required for this certificate.',
            'solo_parent_circumstance.required' => 'Solo parent circumstance is required.',
            'children.required' => 'Add at least one child for the Affidavit of Solo Parent.',
            'children.min' => 'Add at least one child for the Affidavit of Solo Parent.',
            'children.max' => 'A certificate can list at most 10 children.',
            'children.*.child_name.required' => 'Child name is required.',
            'children.*.date_of_birth.required' => 'Child date of birth is required.',
            'children.*.date_of_birth.before_or_equal' => 'Child date of birth cannot be in the future.',
            'overrides.resident_name.max' => 'Resident name must not exceed 150 characters.',
            'overrides.address.max' => 'Address must not exceed 150 characters.',
            'overrides.requested_by.max' => 'Requester must not exceed 150 characters.',
            'attach_pb_signature.boolean' => 'Attach Punong Barangay digital signature must be yes or no.',
        ];
    }

    protected function signatureAfter(): Closure
    {
        return function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty() || ! $this->boolean('attach_pb_signature')) {
                return;
            }

            $typeId = (int) $this->input('type_of_certificate_id');
            $config = config('certificate_types.'.$typeId);
            $allows = is_array($config) && (bool) ($config['allows_signature'] ?? false);

            if (! $allows) {
                $validator->errors()->add(
                    'attach_pb_signature',
                    'Not available for this certificate type.',
                );
            }
        };
    }

    protected function typeSpecificAfter(): Closure
    {
        return function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $kind = $this->certificateKind();

            if ($kind === 'death') {
                $this->assertDeathDate($validator);
            }

            $includeAmount = $this->boolean('include_income_amount');
            $amount = $this->input('stated_income_amount');
            $hasAmount = $amount !== null && $amount !== '';

            if ($kind === 'low_income' && $hasAmount) {
                $validator->errors()->add(
                    'stated_income_amount',
                    'Leave the annual income threshold empty for Low Income Certification without an amount.',
                );
            }

            if (! in_array($kind, ['low_income', 'low_income_amount'], true) && ($includeAmount || $hasAmount)) {
                $validator->errors()->add(
                    'include_income_amount',
                    'A stated income amount is only collected for Low Income Certification (with amount).',
                );
            }
        };
    }

    private function assertDeathDate(Validator $validator): void
    {
        $raw = $this->input('date_of_death');

        if (! is_string($raw) || $raw === '') {
            return;
        }

        try {
            $death = Carbon::parse($raw)->startOfDay();
        } catch (\Throwable) {
            return;
        }

        $residentId = (int) $this->input('resident_id');
        $birth = Resident::query()->whereKey($residentId)->value('date_of_birth');

        if ($birth === null || $birth === '') {
            return;
        }

        $born = Carbon::parse((string) $birth)->startOfDay();

        if ($death->lt($born)) {
            $validator->errors()->add(
                'date_of_death',
                'Date of death cannot be before the resident\'s date of birth.',
            );
        }
    }

    private function needsIncomeAmount(): bool
    {
        return $this->certificateKind() === 'low_income_amount';
    }

    private function needsResidencyYear(): bool
    {
        return $this->certificateKind() === 'low_income';
    }

    private function needsCivilStatus(): bool
    {
        return $this->certificateKind() === 'good_moral' || $this->needsResidencyYear();
    }

    /**
     * @return array<int, mixed>
     */
    private function soloPersonRules(string $field, string $label, bool $strict): array
    {
        return [
            Rule::requiredIf(fn (): bool => $strict && $this->certificateKind() === 'solo_parent'),
            Rule::prohibitedIf(fn (): bool => $this->filled($field) && $this->certificateKind() !== 'solo_parent'),
            'nullable',
            'string',
            'max:100',
            $this->personTextRule($label),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function soloAddressRules(string $field, string $label, bool $strict): array
    {
        return [
            Rule::requiredIf(fn (): bool => $strict && $this->certificateKind() === 'solo_parent'),
            Rule::prohibitedIf(fn (): bool => $this->filled($field) && $this->certificateKind() !== 'solo_parent'),
            'nullable',
            'string',
            'max:255',
            $this->letterRule($label),
        ];
    }

    private function certificateKind(): ?string
    {
        $config = config('certificate_types.'.(int) $this->input('type_of_certificate_id'));
        $kind = is_array($config) ? ($config['kind'] ?? null) : null;

        return is_string($kind) && $kind !== '' ? $kind : null;
    }

    private function purposeRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value)) {
                return;
            }

            if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
                $fail('Purpose of request cannot contain control characters.');

                return;
            }

            if (preg_match('/\p{L}/u', $value) !== 1) {
                $fail('Purpose of request must contain at least one letter.');
            }
        };
    }

    private function personTextRule(string $label): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($label): void {
            if (! is_string($value) || trim($value) === '') {
                return;
            }

            if (preg_match('/\p{N}/u', $value) === 1) {
                $fail($label.' must contain letters and cannot contain digits.');

                return;
            }

            if (preg_match('/\p{L}/u', $value) !== 1 || preg_match('/^[\p{L}\s.\'’\-,]+$/u', $value) !== 1) {
                $fail($label.' may contain only letters, spaces, periods, hyphens, apostrophes, and commas.');
            }
        };
    }

    private function letterRule(string $label): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($label): void {
            if (! is_string($value) || trim($value) === '') {
                return;
            }

            if (preg_match('/\p{L}/u', $value) !== 1) {
                $fail($label.' must contain at least one letter.');
            }
        };
    }

    /**
     * @return array<string, ?string>
     */
    public function overrideValues(): array
    {
        $overrides = $this->validated('overrides');

        if (! is_array($overrides)) {
            return [];
        }

        return array_filter(
            $overrides,
            fn (mixed $value): bool => is_string($value) && trim($value) !== '',
        );
    }
}
