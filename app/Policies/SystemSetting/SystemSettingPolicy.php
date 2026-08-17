<?php

namespace App\Policies\SystemSetting;

use App\Models\Setting\Setting;
use App\Models\UserManagement\User;

class SystemSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('setting.view');
    }

    public function update(User $user, Setting $setting): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('setting.update');
    }
}
