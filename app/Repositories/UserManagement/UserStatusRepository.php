<?php

namespace App\Repositories\UserManagement;

use App\Models\UserManagement\UserStatus;
use App\Repositories\Interfaces\UserManagement\UserStatusInterface;
use Illuminate\Database\Eloquent\Collection;

class UserStatusRepository implements UserStatusInterface
{
    /**
     * @return Collection<int, UserStatus>
     */
    public function all(): Collection
    {
        return UserStatus::query()->orderBy('user_status')->get();
    }
}
