<?php

namespace App\Repositories\Interfaces\UserManagement;

use App\Models\UserManagement\UserStatus;
use Illuminate\Database\Eloquent\Collection;

interface UserStatusInterface
{
    /**
     * @return Collection<int, UserStatus>
     */
    public function all(): Collection;
}
