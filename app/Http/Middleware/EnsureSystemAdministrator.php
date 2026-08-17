<?php

namespace App\Http\Middleware;

use App\Models\UserManagement\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemAdministrator
{
    /**
     * Restrict access to system administrator modules.
     *
     * Super Admin and Admin roles take precedence over Guest when multiple
     * roles are assigned to the same account.
     *
     * Usage: ->middleware('system.admin')
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isSystemAdministrator()) {
            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}
