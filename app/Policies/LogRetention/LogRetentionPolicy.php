<?php

namespace App\Policies\LogRetention;

use App\Models\UserManagement\User;

class LogRetentionPolicy
{
    /**
     * View retention periods and live or archived counts.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('retention.view');
    }

    /**
     * Change audit and user log retention periods.
     */
    public function update(User $user): bool
    {
        return $user->hasPermission('retention.update');
    }

    /**
     * Preview or move expired log rows into the archive tables.
     */
    public function archive(User $user): bool
    {
        return $user->hasPermission('retention.archive');
    }
}
