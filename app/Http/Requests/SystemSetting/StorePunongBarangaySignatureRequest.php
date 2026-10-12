<?php

namespace App\Http\Requests\SystemSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePunongBarangaySignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'signature' => ['required', 'file', 'mimes:png', 'max:1024'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'signature.required' => 'A Punong Barangay signature file is required.',
            'signature.file' => 'The signature must be a PNG image.',
            'signature.mimes' => 'The signature must be a PNG image.',
            'signature.max' => 'The signature image must not be larger than 1024 KB.',
        ];
    }

    /**
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->hasFile('signature')) {
                    return;
                }

                $file = $this->file('signature');
                $info = @getimagesize($file->getPathname());

                if ($info === false || ($info[2] ?? null) !== IMAGETYPE_PNG) {
                    $validator->errors()->add('signature', 'The signature must be a real PNG image.');

                    return;
                }

                $width = (int) ($info[0] ?? 0);

                if ($width < 100 || $width > 1500) {
                    $validator->errors()->add(
                        'signature',
                        'The signature image width must be between 100 and 1500 pixels.',
                    );
                }
            },
        ];
    }
}
