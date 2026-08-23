<?php

namespace App\Services\UserManagement;

use App\Models\Logs\Action;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserRole;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\RoleInterface;
use App\Repositories\Interfaces\UserManagement\UserRoleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserRoleService
{
    public function __construct(
        protected UserRoleRepositoryInterface $userRoleRepository,
        protected AuditLogRepositoryInterface $auditLogRepository,
        protected RoleInterface $roleRepository,
    ) {}

    /**
     * Assign a role to a user. The authenticated admin is recorded as assigned_by.
     */
    public function assignRole(User $assignedBy, User $user, int $roleId): UserRole
    {
        if ($this->roleRepository->findById($roleId) === null) {
            throw ValidationException::withMessages([
                'role_id' => ['The selected role does not exist.'],
            ]);
        }

        if ($this->userRoleRepository->existsForUserAndRole($user->user_id, $roleId)) {
            throw ValidationException::withMessages([
                'role_id' => ['This role is already assigned to the user.'],
            ]);
        }

        return DB::transaction(function () use ($assignedBy, $user, $roleId) {
            $userRole = $this->userRoleRepository->create([
                'user_id' => $user->user_id,
                'role_id' => $roleId,
                'assigned_at' => now()->toDateTimeString(),
                'assigned_by' => $assignedBy->user_id,
                'enable' => true,
            ]);

            $userRole->load(['role', 'assignedBy']);

            $this->auditLogRepository->log(
                performedByUserId: $assignedBy->user_id,
                actionId: Action::CREATE,
                recordId: $userRole->user_role_id,
                description: 'Assign role to user',
                oldValue: null,
                newValue: (string) $roleId,
                target: 'assignment',
                entity: 'user role',
            );

            return $userRole;
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAssignments(User $user): array
    {
        return $this->userRoleRepository
            ->getByUserId($user->user_id)
            ->filter(fn (UserRole $userRole) => $userRole->role !== null)
            ->map(fn (UserRole $userRole) => $this->formatUserRole($userRole))
            ->values()
            ->all();
    }

    public function updateStatus(User $performedBy, UserRole $userRole, bool $enable): UserRole
    {
        $oldEnable = (string) (int) $userRole->enable;

        return DB::transaction(function () use ($performedBy, $userRole, $enable, $oldEnable) {
            $updated = $this->userRoleRepository->updateEnableStatus($userRole, $enable);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $updated->user_role_id,
                description: 'Updated user role status',
                oldValue: $oldEnable,
                newValue: (string) (int) $enable,
                target: 'status',
                entity: 'user role',
            );

            return $updated;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatUserRole(UserRole $userRole): array
    {
        $userRole->loadMissing(['role', 'assignedBy']);

        return [
            'user_role_id' => $userRole->user_role_id,
            'user_id' => $userRole->user_id,
            'role_id' => $userRole->role_id,
            'role_name' => $userRole->role?->role_name,
            'assigned_at' => $userRole->assigned_at,
            'assigned_by' => $userRole->assigned_by,
            'assigned_by_username' => $userRole->assignedBy?->username,
            'enable' => $userRole->enable,
        ];
    }
}
