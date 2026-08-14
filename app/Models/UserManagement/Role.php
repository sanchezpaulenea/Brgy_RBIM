<?php

namespace App\Models\UserManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const ENCODER = 'Encoder';

    public const ADMIN = 'Admin';

    public const SUPER_ADMIN = 'Super Admin';

    public const GUEST = 'Guest';

    /**
     * Roles that may access system administrator modules.
     *
     * @var list<string>
     */
    public const SYSTEM_ADMINISTRATOR_ROLES = [
        self::ADMIN,
        self::SUPER_ADMIN,
    ];

    protected $table = 'role';

    protected $primaryKey = 'role_id';

    public $timestamps = false;

    protected $fillable = ['role_name'];

    /**
     * @return HasMany<RolePermission, $this>
     */
    public function rolePermissions(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'role_id');
    }

    /**
     * @return HasMany<UserRole, $this>
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class, 'role_id', 'role_id');
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
            'role_id',
            'permission_id',
            'role_id',
            'permission_id'
        )->withPivot('role_permission_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_role',
            'role_id',
            'user_id',
            'role_id',
            'user_id'
        )->wherePivot('enable', 1);
    }
}
