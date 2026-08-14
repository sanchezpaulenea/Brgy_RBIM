<?php

namespace App\Repositories\UserManagement;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserRole;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @return Collection<int, User>
     */
    public function all(): Collection
    {
        return User::query()
            ->with(['userStatus', 'personnel.position', 'roles'])
            ->orderBy('username')
            ->get();
    }

    public function findById(int $userId): ?User
    {
        return User::query()
            ->with(['userStatus', 'personnel.position', 'roles', 'userRoles.role', 'userRoles.assignedBy'])
            ->where('user_id', $userId)
            ->first();
    }

    public function findByUsername(string $username): ?User
    {
        return User::with(['userStatus', 'roles.permissions'])
            ->where('username', $username)
            ->first();
    }

    public function lockUserAccount(int $userId): void
    {
        User::where('user_id', $userId)->update(['user_status_id' => 3]);
    }

    public function unlockUserAccount(int $userId): void
    {
        User::where('user_id', $userId)->update(['user_status_id' => 1]);
    }

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function updateStatus(User $user, int $userStatusId): User
    {
        $user->user_status_id = $userStatusId;
        $user->save();

        return $user->fresh(['userStatus']);
    }

    public function resetPassword(User $user, string $plainPassword): User
    {
        $user->password_hash = Hash::make($plainPassword);
        $user->must_change_password = true;
        $user->save();

        return $user->fresh();
    }

    public function personnelIsLinked(int $personnelId, ?int $exceptUserId = null): bool
    {
        return User::query()
            ->where('personnel_id', $personnelId)
            ->when($exceptUserId !== null, fn ($query) => $query->where('user_id', '!=', $exceptUserId))
            ->exists();
    }

    /**
     * @return Collection<int, BarangayPersonnel>
     */
    public function listUnlinkedPersonnel(): Collection
    {
        return BarangayPersonnel::query()
            ->with('position')
            ->whereDoesntHave('user')
            ->join('personnel_position', 'personnel_position.position_id', '=', 'barangay_personnel.position_id')
            ->orderBy('personnel_position.position_name')
            ->select('barangay_personnel.*')
            ->get();
    }

    public function reassignRoleAssignor(int $fromUserId, int $toUserId): void
    {
        UserRole::query()
            ->where('assigned_by', $fromUserId)
            ->update(['assigned_by' => $toUserId]);
    }

    public function deleteRoleAssignments(User $user): void
    {
        $user->userRoles()->delete();
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function hasLoginHistory(int $userId): bool
    {
        return DB::table('user_log')->where('user_id', $userId)->exists();
    }

    public function hasAssignedRolesToOthers(int $userId): bool
    {
        return UserRole::query()
            ->where('assigned_by', $userId)
            ->where('user_id', '!=', $userId)
            ->exists();
    }

    /**
     * @return Collection<int, PersonnelPosition>
     */
    public function listAllPositions(): Collection
    {
        return PersonnelPosition::query()
            ->orderBy('position_name')
            ->get();
    }

    public function findUnlinkedPersonnelByPosition(int $positionId): ?BarangayPersonnel
    {
        return BarangayPersonnel::query()
            ->with('position')
            ->where('position_id', $positionId)
            ->whereDoesntHave('user')
            ->first();
    }

    public function createPersonnelForPosition(int $positionId, string $positionName): BarangayPersonnel
    {
        return BarangayPersonnel::create([
            'position_id' => $positionId,
            'personnel_first_name' => mb_substr($positionName, 0, 45),
            'personnel_last_name' => 'Personnel',
            'personnel_status_id' => 1,
            'personnel_date_of_birth' => '1990-01-01',
        ]);
    }
}
