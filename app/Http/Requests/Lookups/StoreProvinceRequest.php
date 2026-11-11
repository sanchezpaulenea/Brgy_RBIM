<?php

namespace App\Http\Requests\Lookups;

use App\Rules\ValidPlaceName;
use Illuminate\Foundation\Http\FormRequest;

class StoreProvinceRequest extends FormRequest
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
            'province_name' => ['required', 'string', 'max:60', new ValidPlaceName('Province')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'province_name.required' => 'Province is required.',
            'province_name.max' => 'Province must be 60 characters or fewer.',
        ];
    }
}
