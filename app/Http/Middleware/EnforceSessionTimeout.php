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

        $concurrencyMessage = $this->authenticationService->enforceConcurrentSession($user, $request);

        if ($concurrencyMessage !== null) {
            return response()->json([
                'message' => $concurrencyMessage,
                'reason' => AuthenticationService::LOGOUT_REASON_CONCURRENT_LOGIN,
            ], 401);
        }

        if (! $this->authenticationService->enforceSessionTimeout($user, $request)) {
            return response()->json([
                'message' => AuthenticationService::IDLE_TIMEOUT_MESSAGE,
                'reason' => AuthenticationService::LOGOUT_REASON_IDLE_TIMEOUT,
            ], 401);
        }

        return $next($request);
    }
}
