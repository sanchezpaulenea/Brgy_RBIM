<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\LoginRequest;
use App\Models\Authentication\UserLog;
use App\Models\UserManagement\User;
use App\Services\Authentication\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authenticationService
    ) {}

    /**
     * POST /api/v1/auth/login
     *
     * No Policy — the user is not authenticated yet.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authenticationService->login(
            $request->validated('username'),
            $request->validated('password'),
            $request
        );

        return response()->json([
            'message' => 'Login successful.',
            'user' => [
                'user_id' => $result['user']->user_id,
                'username' => $result['user']->username,
                'must_change_password' => $result['must_change_password'],
            ],
            'roles' => $result['roles'],
            'permissions' => $result['permissions'],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authorize('logout', $user);

        $this->authenticationService->logout($user, $request);

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['userStatus', 'roles.permissions']);

        return response()->json([
            'user' => [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'must_change_password' => $user->must_change_password,
                'user_status' => $user->userStatus->user_status,
                'created_at' => $user->created_at,
            ],
            'roles' => $user->roles->pluck('role_name'),
            'permissions' => $user->permissions()->pluck('permission'),
        ]);
    }

    /**
     * GET /api/v1/auth/logs
     */
    public function userLogs(Request $request): JsonResponse
    {
        $this->authorize('viewAny', UserLog::class);

        return response()->json([
            'logs' => $this->authenticationService->listLoginHistory(),
        ]);
    }
}
