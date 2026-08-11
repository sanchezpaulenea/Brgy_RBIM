<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\LoginRequest;
use App\Services\Authentication\AuthService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Authenticate a user and start a stateful session.
     *
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
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
}
