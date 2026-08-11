<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\UserManagement\User;
use App\Services\Authentication\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Invalidate the current session and record logout time in user_log.
     *
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authService->logout($user, $request);

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
