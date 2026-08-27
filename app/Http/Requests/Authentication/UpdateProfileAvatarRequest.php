<?php

namespace App\Http\Requests\Authentication;

use App\Models\UserManagement\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProfileAvatarRequest extends FormRequest
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
            'photo' => ['nullable', 'file', 'image', 'max:2048', 'mimes:jpeg,jpg,png,webp,gif'],
            'avatar_preset' => ['nullable', 'string', Rule::in([User::AVATAR_MALE, User::AVATAR_FEMALE])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.image' => 'The profile photo must be an image.',
            'photo.max' => 'The profile photo must not be larger than 2 MB.',
            'photo.mimes' => 'The profile photo must be a JPEG, PNG, WebP, or GIF image.',
            'avatar_preset.in' => 'Choose a male or female avatar.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! $this->hasFile('photo') && ! $this->filled('avatar_preset')) {
                    $validator->errors()->add(
                        'photo',
                        'Upload a photo or choose a male or female avatar.'
                    );
                }
            },
        ];
    }
}
