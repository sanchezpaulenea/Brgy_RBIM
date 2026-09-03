<?php

namespace App\Models\UserManagement;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\Logs\UserLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
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

    public const AVATAR_MALE = 'male';

    public const AVATAR_FEMALE = 'female';

    protected $fillable = [
        'username',
        'password_hash',
        'user_status_id',
        'personnel_id',
        'must_change_password',
        'avatar_path',
        'avatar_preset',
    ];

    /**
     * Persist usernames in lowercase so lookups stay case-insensitive.
     */
    public static function standardizeUsername(string $username): string
    {
        return mb_strtolower(trim($username), 'UTF-8');
    }

    /**
     * Present a stored username without forcing the whole value to lowercase.
     */
    public static function formatForDisplay(string $username): string
    {
        $trimmed = trim($username);

        if ($trimmed === '') {
            return $trimmed;
        }

        return preg_replace_callback('/\S+/u', function (array $matches): string {
            $word = $matches[0];

            return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
                .mb_substr($word, 1, null, 'UTF-8');
        }, $trimmed) ?? $trimmed;
    }

    public function setUsernameAttribute(mixed $value): void
    {
        $this->attributes['username'] = is_string($value)
            ? self::standardizeUsername($value)
            : $value;
    }

    protected $hidden = [
        'password_hash',
        'avatar_path',
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
     * Given name then family name, matching how the profile header is read.
     * Falls back to the display username when no personnel record is linked.
     */
    public function profileDisplayName(): string
    {
        $personnel = $this->personnel;

        if ($personnel === null) {
            return self::formatForDisplay((string) $this->username);
        }

        $parts = collect([
            $personnel->personnel_first_name,
            $personnel->personnel_middle_name,
            $personnel->personnel_last_name,
        ])
            ->filter(fn (mixed $part) => is_string($part) && trim($part) !== '')
            ->values();

        if ($parts->isEmpty()) {
            return self::formatForDisplay((string) $this->username);
        }

        return $parts->implode(' ');
    }

    public function profileGivenName(): string
    {
        $first = trim((string) ($this->personnel?->personnel_first_name ?? ''));
        $middle = trim((string) ($this->personnel?->personnel_middle_name ?? ''));

        return trim($first.' '.$middle);
    }

    public function profileFamilyName(): string
    {
        return trim((string) ($this->personnel?->personnel_last_name ?? ''));
    }

    public function profilePositionName(): ?string
    {
        $position = $this->personnel?->position?->position_name;

        return is_string($position) && $position !== '' ? $position : null;
    }

    public function avatarUrl(): ?string
    {
        if (! is_string($this->avatar_path) || $this->avatar_path === '') {
            return null;
        }

        $version = Storage::disk('local')->exists($this->avatar_path)
            ? (string) Storage::disk('local')->lastModified($this->avatar_path)
            : substr(md5($this->avatar_path), 0, 8);

        return '/api/v1/auth/profile/avatar?v='.$version;
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
     * Distinct permissions granted through every enabled role assignment.
     *
     * Joins all `user_role` rows where enable = 1 for this user, then unions
     * `role_permission` across those roles (no duplicates).
     *
     * @return Collection<int, Permission>
     */
    public function permissions(): Collection
    {
        return $this->enabledPermissionQuery()
            ->orderBy('permission')
            ->get();
    }

    /**
     * Checks whether the user has a specific permission slug via any enabled role.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->enabledPermissionQuery()
            ->where('permission', $permission)
            ->exists();
    }

    /**
     * Permission rows reachable through user_role.enable = 1 for this user.
     */
    protected function enabledPermissionQuery(): Builder
    {
        return Permission::query()->whereHas('roles', function (Builder $query): void {
            $query->whereHas('userRoles', function (Builder $query): void {
                $query->where('user_id', $this->user_id)->where('enable', 1);
            });
        });
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

    public function isEncoder(): bool
    {
        return $this->hasRole(Role::ENCODER);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    /**
     * Encoders need reference lists (streets, sex, nationality, and so on)
     * while registering a household, even when they cannot manage those tables.
     */
    public function canListResidentReferenceData(): bool
    {
        return $this->isEncoder()
            || $this->hasPermission('household.view')
            || $this->hasPermission('resident.view');
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
