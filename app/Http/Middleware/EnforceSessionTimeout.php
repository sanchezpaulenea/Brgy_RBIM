<?php

namespace App\Http\Middleware;

use App\Models\UserManagement\User;
use App\Services\Authentication\AuthenticationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionTimeout
{
    public function __construct(
        protected AuthenticationService $authenticationService,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $accessMessage = $this->authenticationService->enforceAccountAccess($user, $request);

        if ($accessMessage !== null) {
            return response()->json([
                'message' => $accessMessage,
            ], 401);
        }

        if (! $this->authenticationService->enforceSessionTimeout($user, $request)) {
            return response()->json([
                'message' => 'Session expired due to inactivity. Please log in again.',
            ], 401);
        }

        return $next($request);
    }
}
