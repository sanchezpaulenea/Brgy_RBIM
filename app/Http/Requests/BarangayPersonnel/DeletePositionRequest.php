<?php

namespace App\Http\Requests\BarangayPersonnel;

use Illuminate\Foundation\Http\FormRequest;

class DeletePositionRequest extends FormRequest
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
        return [];
    }
}
