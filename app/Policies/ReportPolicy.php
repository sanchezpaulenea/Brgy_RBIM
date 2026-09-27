<?php

namespace App\Policies;

use App\Models\UserManagement\User;

/**
 * Authorizes report generation.
 * Registered in AuthServiceProvider.
 */
class ReportPolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('report.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('report.create');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('report.update');
    }
}
