<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Requests\Authentication\UpdateProfileAvatarRequest;
use App\Models\UserManagement\User;
use App\Services\Authentication\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'user' => $this->authenticationService->sessionUser($result['user']),
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
        $user = $request->user()->load(['userStatus', 'roles.permissions', 'personnel.position']);

        return response()->json($this->authenticationService->sessionPayload($user, includeStatus: true));
    }

    /**
     * POST /api/v1/auth/profile/avatar
     */
    public function updateAvatar(UpdateProfileAvatarRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authorize('updateAvatar', $user);

        $updated = $this->authenticationService->updateAvatar(
            $user,
            $request->file('photo'),
            $request->validated('avatar_preset')
        );

        $updated->load(['userStatus', 'roles.permissions', 'personnel.position']);

        return response()->json([
            'message' => 'Profile photo updated.',
            ...$this->authenticationService->sessionPayload($updated, includeStatus: true),
        ]);
    }

    /**
     * GET /api/v1/auth/profile/avatar
     */
    public function avatar(Request $request): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->authenticationService->avatarResponse($user);
    }
}
