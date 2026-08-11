<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\ChangePasswordRequest;
use App\Models\UserManagement\User;
use App\Services\Authentication\AuthService;
use Illuminate\Http\JsonResponse;

class PasswordController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Change the authenticated user's own password.
     *
     * POST /api/v1/auth/password/change
     */
    public function change(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authorize('changePassword', $user);

        $this->authService->changePassword(
            $user,
            $request->validated('current_password'),
            $request->validated('new_password')
        );

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
