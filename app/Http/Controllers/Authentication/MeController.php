<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\UserManagement\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Return the authenticated user's profile, active roles, and permissions.
     *
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
}
