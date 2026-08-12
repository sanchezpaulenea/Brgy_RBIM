<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserManagement\StoreUserRequest;
use App\Http\Requests\UserManagement\UpdateUserStatusRequest;
use App\Models\UserManagement\User;
use App\Services\UserManagement\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * GET /api/v1/users
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        return response()->json([
            'users' => $this->userService->listUsers(),
        ]);
    }

    /**
     * POST /api/v1/users
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $user = $this->userService->createUser($performedBy, $request->validated());

        return response()->json([
            'message' => 'User account created successfully.',
            'user' => $user,
        ], 201);
    }

    /**
     * GET /api/v1/users/{user}
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return response()->json([
            'user' => $this->userService->getUser($user),
        ]);
    }

    /**
     * PATCH /api/v1/users/{user}/status
     */
    public function updateStatus(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        $this->authorize('updateStatus', $user);

        /** @var User $performedBy */
        $performedBy = $request->user();

        $updated = $this->userService->updateStatus(
            $performedBy,
            $user,
            $request->validated('user_status_id')
        );

        return response()->json([
            'message' => 'User status updated successfully.',
            'user' => $updated,
        ]);
    }

    /**
     * POST /api/v1/users/{user}/reset-password
     */
    public function resetPassword(User $user): JsonResponse
    {
        $this->authorize('resetPassword', $user);

        /** @var User $performedBy */
        $performedBy = request()->user();

        $updated = $this->userService->resetPassword($performedBy, $user);

        return response()->json([
            'message' => 'User password reset successfully.',
            'user' => $updated,
        ]);
    }

    /**
     * GET /api/v1/users/create-options
     */
    public function createOptions(): JsonResponse
    {
        $this->authorize('create', User::class);

        return response()->json($this->userService->getCreateOptions());
    }

    /**
     * DELETE /api/v1/users/{user}
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        /** @var User $performedBy */
        $performedBy = request()->user();

        $this->userService->deleteUser($performedBy, $user);

        return response()->json([
            'message' => 'User account deleted successfully.',
        ]);
    }
}
