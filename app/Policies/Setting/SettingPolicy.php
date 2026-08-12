<?php

namespace App\Policies\Setting;

use App\Models\Setting\Setting;
use App\Models\UserManagement\User;

/**
 * Authorizes system setting management actions.
 * Registered in AuthServiceProvider.
 */
class SettingPolicy
{
    /**
     * List all system settings.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('setting.view');
    }

    /**
     * Update a system setting value.
     */
    public function update(User $user, Setting $setting): bool
    {
        return $user->isSystemAdministrator() && $user->hasPermission('setting.update');
    }
}
