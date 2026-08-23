<?php

namespace App\Services\UserManagement;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\Logs\Action;
use App\Models\UserManagement\Role;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserStatus;
use App\Repositories\Interfaces\Logs\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\RoleInterface;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserStatusInterface;
use App\Services\SystemSetting\SystemSettingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class UserService
{
    public const ACTIVE_STATUS_ID = 1;

    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected UserRoleService $userRoleService,
        protected AuditLogRepositoryInterface $auditLogRepository,
        protected SystemSettingService $settingService,
        protected RoleInterface $roleRepository,
        protected UserStatusInterface $userStatusRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listUsers(): array
    {
        return $this->userRepository
            ->all()
            ->map(fn (User $user) => $this->formatUser($user))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getUser(User $user): array
    {
        $fresh = $this->userRepository->findById($user->user_id) ?? $user;

        return $this->formatUser($fresh);
    }

    /**
     * Create a user account and assign the initial role in one transaction.
     *
     * @param  array{username: string, role_id: int, personnel_id?: int, position_id?: int}  $data
     * @return array<string, mixed>
     */
    public function createUser(User $performedBy, array $data): array
    {
        $defaultPassword = (string) $this->settingService->get('default_password');

        return DB::transaction(function () use ($performedBy, $data, $defaultPassword) {
            $personnelId = $this->resolvePersonnelId($data);

            $user = $this->userRepository->create([
                'username' => User::standardizeUsername($data['username']),
                'password_hash' => Hash::make($defaultPassword),
                'user_status_id' => self::ACTIVE_STATUS_ID,
                'personnel_id' => $personnelId,
                'must_change_password' => true,
            ]);

            $this->userRoleService->assignRole($performedBy, $user, $data['role_id']);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::CREATE,
                recordId: $user->user_id,
                description: 'Create user account',
                oldValue: null,
                newValue: $user->username,
                target: 'account',
                entity: 'user',
            );

            $fresh = $this->userRepository->findById($user->user_id);

            return $this->formatUser($fresh ?? $user);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function updateStatus(User $performedBy, User $user, int $userStatusId): array
    {
        $oldStatusId = (string) $user->user_status_id;

        return DB::transaction(function () use ($performedBy, $user, $userStatusId, $oldStatusId) {
            $updated = $this->userRepository->updateStatus($user, $userStatusId);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $updated->user_id,
                description: 'Updated user account status',
                oldValue: $oldStatusId,
                newValue: (string) $userStatusId,
                target: 'status',
                entity: 'user',
            );

            $fresh = $this->userRepository->findById($updated->user_id);

            return $this->formatUser($fresh ?? $updated);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function resetPassword(User $performedBy, User $user): array
    {
        $defaultPassword = (string) $this->settingService->get('default_password');

        return DB::transaction(function () use ($performedBy, $user, $defaultPassword) {
            $updated = $this->userRepository->resetPassword($user, $defaultPassword);

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $updated->user_id,
                description: 'Reset user password',
                oldValue: null,
                newValue: $updated->username,
                target: 'password',
                entity: 'user',
            );

            $fresh = $this->userRepository->findById($updated->user_id);

            return $this->formatUser($fresh ?? $updated);
        });
    }

    /**
     * @return array{roles: array<int, array{role_id: int, role_name: string}>, personnel: array<int, array{personnel_id: int, position_id: int, label: string}>, positions: array<int, array{position_id: int, label: string}>}
     */
    public function getCreateOptions(): array
    {
        $roles = $this->roleRepository
            ->all()
            ->map(fn (Role $role) => [
                'role_id' => $role->role_id,
                'role_name' => $role->role_name,
            ])
            ->all();

        $personnel = $this->userRepository
            ->listUnlinkedPersonnel()
            ->map(fn ($record) => [
                'personnel_id' => $record->personnel_id,
                'position_id' => $record->position_id,
                'position_name' => $this->formatPersonnelPositionLabel($record),
                'label' => $this->formatPersonnelName($record),
            ])
            ->all();

        $positions = $this->userRepository
            ->listAllPositions()
            ->map(fn ($position) => [
                'position_id' => $position->position_id,
                'label' => $position->position_name,
            ])
            ->all();

        return [
            'roles' => $roles,
            'personnel' => $personnel,
            'positions' => $positions,
        ];
    }

    /**
     * @return array<int, array{id: int, label: string, can_login: bool}>
     */
    public function listUserStatuses(): array
    {
        return $this->userStatusRepository
            ->all()
            ->map(fn (UserStatus $status) => [
                'id' => $status->user_status_id,
                'label' => $status->user_status,
                'can_login' => $status->can_login,
            ])
            ->all();
    }

    public function deleteUser(User $performedBy, User $user): void
    {
        if ($performedBy->user_id === $user->user_id) {
            throw new ConflictHttpException('You cannot delete your own account.');
        }

        if ($this->userRepository->hasLoginHistory($user->user_id)) {
            throw new ConflictHttpException('Cannot delete an account with login history. Disable the account instead.');
        }

        if ($this->auditLogRepository->hasEntriesForUser($user->user_id)) {
            throw new ConflictHttpException('Cannot delete an account with audit history. Disable the account instead.');
        }

        $username = $user->username;

        DB::transaction(function () use ($performedBy, $user, $username) {
            if ($this->userRepository->hasAssignedRolesToOthers($user->user_id)) {
                $this->userRepository->reassignRoleAssignor($user->user_id, $performedBy->user_id);
            }

            $this->userRepository->deleteRoleAssignments($user);

            if (! $this->userRepository->delete($user)) {
                throw new ConflictHttpException('Unable to delete the user account.');
            }

            $this->auditLogRepository->log(
                performedByUserId: $performedBy->user_id,
                actionId: Action::UPDATE,
                recordId: $user->user_id,
                description: 'Delete user account',
                oldValue: $username,
                newValue: '',
                target: 'account',
                entity: 'user',
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function formatUser(User $user): array
    {
        $user->loadMissing(['userStatus', 'personnel.position', 'roles', 'userRoles.role']);

        return [
            'user_id' => $user->user_id,
            'username' => User::formatForDisplay($user->username),
            'user_status_id' => $user->user_status_id,
            'user_status' => $user->userStatus?->user_status,
            'personnel_id' => $user->personnel_id,
            'personnel' => $user->personnel ? [
                'personnel_id' => $user->personnel->personnel_id,
                'position_id' => $user->personnel->position_id,
                'position_name' => $user->personnel->position?->position_name,
                'full_name' => $this->formatPersonnelName($user->personnel),
            ] : null,
            'must_change_password' => $user->must_change_password,
            'created_at' => $user->created_at,
            'roles' => $user->roles->pluck('role_name')->filter()->values()->all(),
            'role_assignments' => $user->userRoles
                ->filter(fn ($assignment) => $assignment->role !== null)
                ->map(fn ($assignment) => [
                    'user_role_id' => $assignment->user_role_id,
                    'role_id' => $assignment->role_id,
                    'role_name' => $assignment->role->role_name,
                    'enable' => (bool) $assignment->enable,
                ])
                ->values()
                ->all(),
        ];
    }

    private function formatPersonnelPositionLabel(BarangayPersonnel $personnel): string
    {
        return $personnel->position?->position_name ?? 'Unassigned position';
    }

    private function formatPersonnelName(BarangayPersonnel $personnel): string
    {
        $givenNames = collect([
            $personnel->personnel_first_name,
            $personnel->personnel_middle_name,
            $personnel->personnel_suffix,
        ])->filter()->implode(' ');

        if ($givenNames === '') {
            return (string) $personnel->personnel_last_name;
        }

        return $personnel->personnel_last_name.', '.$givenNames;
    }

    /**
     * @param  array{personnel_id?: int, position_id?: int}  $data
     */
    private function resolvePersonnelId(array $data): ?int
    {
        if (! empty($data['personnel_id'])) {
            return (int) $data['personnel_id'];
        }

        if (empty($data['position_id'])) {
            return null;
        }

        $positionId = (int) $data['position_id'];
        $existing = $this->userRepository->findUnlinkedPersonnelByPosition($positionId);

        if ($existing !== null) {
            return $existing->personnel_id;
        }

        $position = $this->userRepository->listAllPositions()
            ->firstWhere('position_id', $positionId);

        if ($position === null) {
            throw new ConflictHttpException('The selected personnel position does not exist.');
        }

        $personnel = $this->userRepository->createPersonnelForPosition(
            $positionId,
            $position->position_name,
        );

        return $personnel->personnel_id;
    }
}
