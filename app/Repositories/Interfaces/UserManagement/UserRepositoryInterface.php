<?php

namespace App\Repositories\Interfaces\UserManagement;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
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

    public function personnelIsLinked(int $personnelId, ?int $exceptUserId = null): bool;

    /**
     * @return Collection<int, BarangayPersonnel>
     */
    public function listUnlinkedPersonnel(): Collection;

    public function reassignRoleAssignor(int $fromUserId, int $toUserId): void;

    public function deleteRoleAssignments(User $user): void;

    public function delete(User $user): bool;

    public function hasLoginHistory(int $userId): bool;

    public function hasAssignedRolesToOthers(int $userId): bool;

    /**
     * @return Collection<int, PersonnelPosition>
     */
    public function listAllPositions(): Collection;

    public function findUnlinkedPersonnelByPosition(int $positionId): ?BarangayPersonnel;

    public function createPersonnelForPosition(int $positionId, string $positionName): BarangayPersonnel;
}
