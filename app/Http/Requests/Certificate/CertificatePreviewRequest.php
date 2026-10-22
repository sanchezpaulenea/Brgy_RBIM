<?php

namespace App\Http\Requests\Certificate;

use App\Http\Requests\Certificate\Concerns\ValidatesCertificateInput;
use Illuminate\Foundation\Http\FormRequest;

class CertificatePreviewRequest extends FormRequest
{
    use ValidatesCertificateInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareCertificateInput();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->certificateRules(strictTypeFields: false);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->certificateMessages();
    }

    /**
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            $this->typeSpecificAfter(),
        ];
    }
}
