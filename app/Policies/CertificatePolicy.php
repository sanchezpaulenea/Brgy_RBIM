<?php

namespace App\Policies;

use App\Models\UserManagement\User;

/**
 * Authorizes certificate generation.
 * Registered in AuthServiceProvider.
 */
class CertificatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('certificate.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('certificate.create');
    }

    public function edit(User $user): bool
    {
        return $user->hasPermission('certificate.edit');
    }
}
