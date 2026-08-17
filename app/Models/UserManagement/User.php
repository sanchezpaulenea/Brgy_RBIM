<?php

namespace App\Models\UserManagement;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\Logs\UserLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'user';

    protected $primaryKey = 'user_id';

    /**
     * The `user` table only has `created_at`, managed by MySQL DEFAULT CURRENT_TIMESTAMP.
     * Disabling Eloquent timestamps prevents it from writing `updated_at`.
     */
    public $timestamps = false;

    /**
     * Disable remember token — the `user` table has no `remember_token` column.
     *
     * @var string|bool
     */
    public $rememberTokenName = false;

    protected $fillable = [
        'username',
        'password_hash',
        'user_status_id',
        'personnel_id',
        'must_change_password',
    ];

    protected $hidden = [
        'password_hash',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'must_change_password' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * The `user` table stores the password in the `password_hash` column.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * The identifier used for authentication is `username`.
     */
    public function getAuthIdentifierName(): string
    {
        return 'username';
    }

    public function getRouteKeyName(): string
    {
        return 'user_id';
    }

    /**
     * @return BelongsTo<BarangayPersonnel, $this>
     */
    public function personnel(): BelongsTo
    {
        return $this->belongsTo(BarangayPersonnel::class, 'personnel_id', 'personnel_id');
    }

    /**
     * @return BelongsTo<UserStatus, $this>
     */
    public function userStatus(): BelongsTo
    {
        return $this->belongsTo(UserStatus::class, 'user_status_id', 'user_status_id');
    }

    /**
     * @return HasMany<UserRole, $this>
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id', 'user_id');
    }

    /**
     * Active roles assigned to this user (`enable = 1`).
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_role',
            'user_id',
            'role_id',
            'user_id',
            'role_id'
        )
            ->wherePivot('enable', 1)
            ->withPivot(['user_role_id', 'assigned_at', 'assigned_by', 'enable']);
    }

    /**
     * Active role assignments for this user (`enable = 1`).
     *
     * @return HasMany<UserRole, $this>
     */
    public function activeUserRoles(): HasMany
    {
        return $this->userRoles()->where('enable', 1);
    }

    /**
     * Returns the unique permissions across all active roles.
     *
     * Requires `roles.permissions` to be eager-loaded first.
     *
     * @return Collection<int, Permission>
     */
    public function permissions(): Collection
    {
        return new Collection(
            $this->roles
                ->flatMap(fn (Role $role) => $role->permissions)
                ->unique('permission_id')
                ->values()
                ->all()
        );
    }

    /**
     * Checks whether the user has a specific permission slug via active role assignments.
     *
     * Joins: user_role → role_permission → permission (respects user_role.enable).
     */
    public function hasPermission(string $permission): bool
    {
        return Permission::query()
            ->where('permission', $permission)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('user_role')
                    ->join(
                        'role_permission',
                        'role_permission.role_id',
                        '=',
                        'user_role.role_id'
                    )
                    ->whereColumn('role_permission.permission_id', 'permission.permission_id')
                    ->where('user_role.user_id', $this->user_id)
                    ->where('user_role.enable', 1);
            })
            ->exists();
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()
            ->where('role_name', $roleName)
            ->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    /**
     * Guest-only accounts are restricted from system administrator modules.
     * Users who also hold Admin or Super Admin are not treated as guests.
     */
    public function isGuest(): bool
    {
        return $this->hasRole(Role::GUEST) && ! $this->isSystemAdministrator();
    }

    /**
     * Whether the user holds a system administrator role (Admin or Super Admin).
     * Super Admin takes precedence over Guest when both roles are assigned.
     */
    public function isSystemAdministrator(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->hasRole(Role::ADMIN);
    }

    /**
     * @return HasMany<UserLog, $this>
     */
    public function userLogs(): HasMany
    {
        return $this->hasMany(
            UserLog::class,
            'user_id',
            'user_id'
        );
    }
}
