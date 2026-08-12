<?php

namespace App\Services\Authentication;

use App\Models\UserManagement\User;
use App\Repositories\Interfaces\SystemSetting\SystemSettingRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected SystemSettingRepositoryInterface $systemSettingRepository
    ) {}

    /**
     * Change the authenticated user's password.
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password_hash)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $minLength = (int) $this->systemSettingRepository->getValue('password_min_length');

        if (strlen($newPassword) < $minLength) {
            throw ValidationException::withMessages([
                'new_password' => ["Password must be at least {$minLength} characters."],
            ]);
        }

        $user->password_hash = Hash::make($newPassword);
        $user->must_change_password = false;
        $user->save();
    }
}
