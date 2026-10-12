<?php

namespace App\Http\Requests\Certificate\Concerns;

use App\Models\Setting\Setting;
use Closure;
use Illuminate\Support\Facades\Storage;
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
    protected function certificateRules(bool $withSignature = false): array
    {
        $rules = [
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
                'max:45',
                $this->purposeRule(),
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
            'type_of_certificate_id.required' => 'Certificate type is required.',
            'type_of_certificate_id.exists' => 'Select a certificate type.',
            'resident_id.required' => 'Requester is required.',
            'resident_id.exists' => 'Select a resident.',
            'purpose_of_request.required' => 'Purpose of request is required.',
            'purpose_of_request.min' => 'Purpose of request must be at least 3 characters.',
            'purpose_of_request.max' => 'Purpose of request must not exceed 45 characters.',
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

                return;
            }

            $path = (string) config('certificates.signature_path');
            $disk = (string) config('certificates.signature_disk');
            $stored = trim((string) Setting::query()
                ->where('setting_key', 'punong_barangay_signature')
                ->value('setting_value'));

            if ($stored === '' || $stored !== $path || ! Storage::disk($disk)->exists($path)) {
                $validator->errors()->add(
                    'attach_pb_signature',
                    'No Punong Barangay signature uploaded. Ask a Super Admin to upload it in Settings.',
                );
            }
        };
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
