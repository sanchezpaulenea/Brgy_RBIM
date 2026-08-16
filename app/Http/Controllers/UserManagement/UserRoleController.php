<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\StoreUserRoleRequest;
use App\Http\Requests\UserManagement\UpdateUserRoleStatusRequest;
use App\Models\UserManagement\User;
use App\Models\UserManagement\UserRole;
use App\Services\UserManagement\UserRoleService;
use Illuminate\Http\JsonResponse;

class UserRoleController extends Controller
{
    public function __construct(
        protected UserRoleService $userRoleService
    ) {}

    /**
     * GET /api/v1/users/{user}/roles
     */
    public function index(User $user): JsonResponse
    {
        $this->authorize('viewAny', UserRole::class);

        return response()->json([
            'user_id' => $user->user_id,
            'roles' => $this->userRoleService->listAssignments($user),
        ]);
    }

    /**
     * POST /api/v1/users/{user}/roles
     */
    public function store(StoreUserRoleRequest $request, User $user): JsonResponse
    {
        $this->authorize('create', UserRole::class);

        /** @var User $assignedBy */
        $assignedBy = $request->user();

        $userRole = $this->userRoleService->assignRole(
            $assignedBy,
            $user,
            $request->validated('role_id')
        );

        return response()->json([
            'message' => 'Role assigned successfully.',
            'user_role' => $this->userRoleService->formatUserRole($userRole),
        ], 201);
    }

    /**
     * PATCH /api/v1/user-roles/{userRole}/status
     */
    public function updateStatus(UpdateUserRoleStatusRequest $request, UserRole $userRole): JsonResponse
    {
        $this->authorize('updateStatus', $userRole);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $updated = $this->userRoleService->updateStatus(
            $performedBy,
            $userRole,
            $request->boolean('enable')
        );

        return response()->json([
            'message' => 'User role status updated successfully.',
            'user_role' => $this->userRoleService->formatUserRole($updated),
        ]);
    }
}
