<?php

namespace App\Repositories\Interfaces\UserManagement;

use App\Models\UserManagement\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * @return Collection<int, User>
     */
    public function all(): Collection;

    public function findById(int $userId): ?User;

    public function create(array $attributes): User;

    public function updateStatus(User $user, int $userStatusId): User;

    public function resetPassword(User $user, string $plainPassword): User;

    public function getSystemSetting(string $key): string;

    public function personnelIsLinked(int $personnelId, ?int $exceptUserId = null): bool;
}
