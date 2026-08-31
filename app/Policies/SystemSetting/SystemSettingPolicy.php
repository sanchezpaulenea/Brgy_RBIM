<?php

namespace App\Policies\SystemSetting;

use App\Models\Setting\Setting;
use App\Models\UserManagement\User;

class SystemSettingPolicy
{
    /**
     * Province, city, and barangay names are needed on encoding forms.
     */
    public function viewLocationProfile(User $user): bool
    {
        return true;
    }

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->isSuperAdmin();
    }
}
