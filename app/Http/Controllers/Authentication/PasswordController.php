<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\ChangePasswordRequest;
use App\Models\UserManagement\User;
use App\Services\Authentication\AuthenticationService;
use App\Services\SystemSetting\SystemSettingService;
use Illuminate\Http\JsonResponse;

class PasswordController extends Controller
{
    public function __construct(
        protected AuthenticationService $authenticationService,
        protected SystemSettingService $settingService,
    ) {}

    /**
     * Current password rules, so the change-password form validates against the
     * stored setting instead of a stale copy of it.
     *
     * GET /api/v1/auth/password/policy
     */
    public function policy(): JsonResponse
    {
        return response()->json([
            'password_min_length' => (int) $this->settingService->get('password_min_length'),
        ]);
    }

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

        $this->authenticationService->changePassword(
            $user,
            $request->validated('current_password'),
            $request->validated('new_password')
        );

        $this->authenticationService->logout($user, $request);

        return response()->json(['message' => 'Password changed successfully. Please log in with your new password.']);
    }
}
