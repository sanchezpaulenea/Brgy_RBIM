<?php

namespace App\Services\UserManagement;

use App\Models\AuditLog\Action;
use App\Models\UserManagement\User;
use App\Repositories\Interfaces\AuditLog\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\UserManagement\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public const ACTIVE_STATUS_ID = 1;

    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected UserRoleService $userRoleService,
        protected AuditLogRepositoryInterface $auditLogRepository,
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
     * @param  array{username: string, personnel_id: int, role_id: int}  $data
     * @return array<string, mixed>
     */
    public function createUser(User $performedBy, array $data): array
    {
        $defaultPassword = $this->userRepository->getSystemSetting('default_password');

        return DB::transaction(function () use ($performedBy, $data, $defaultPassword) {
            $user = $this->userRepository->create([
                'username' => $data['username'],
                'password_hash' => Hash::make($defaultPassword),
                'user_status_id' => self::ACTIVE_STATUS_ID,
                'personnel_id' => $data['personnel_id'],
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
        $defaultPassword = $this->userRepository->getSystemSetting('default_password');

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
     * @return array<string, mixed>
     */
    public function formatUser(User $user): array
    {
        $user->loadMissing(['userStatus', 'personnel', 'roles', 'userRoles.role']);

        return [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'user_status_id' => $user->user_status_id,
            'user_status' => $user->userStatus?->user_status,
            'personnel_id' => $user->personnel_id,
            'personnel' => $user->personnel ? [
                'personnel_id' => $user->personnel->personnel_id,
                'first_name' => $user->personnel->personnel_first_name,
                'last_name' => $user->personnel->personnel_last_name,
                'middle_name' => $user->personnel->personnel_middle_name,
                'suffix' => $user->personnel->personnel_suffix,
            ] : null,
            'must_change_password' => $user->must_change_password,
            'created_at' => $user->created_at,
            'roles' => $user->roles->pluck('role_name'),
        ];
    }
}
