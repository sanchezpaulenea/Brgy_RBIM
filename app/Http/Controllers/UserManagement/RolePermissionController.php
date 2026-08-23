<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\UserManagement\Role;
use App\Repositories\Interfaces\UserManagement\RoleInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function __construct(
        protected RoleInterface $roleRepository,
    ) {}

    /**
     * GET /api/v1/role-permissions
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('userrole.view'), 403);

        $roles = $this->roleRepository
            ->allWithPermissions()
            ->map(fn (Role $role) => [
                'role_id' => $role->role_id,
                'role_name' => $role->role_name,
                'permissions' => $role->permissions
                    ->pluck('permission')
                    ->values()
                    ->all(),
            ])
            ->all();

        return response()->json(['roles' => $roles]);
    }
}
