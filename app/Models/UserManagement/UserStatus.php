<?php

namespace App\Models\UserManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserStatus extends Model
{
    protected $table = 'user_status';

    protected $primaryKey = 'user_status_id';

    public $timestamps = false;

    protected $fillable = ['user_status', 'can_login'];

    /** Seeded user_status_id constants for use throughout the application. */
    public const ACTIVE = 1;

    public const DISABLED = 2;

    public const LOCKED = 3;

    public const SUSPENDED = 4;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'can_login' => 'boolean',
    ];

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'user_status_id', 'user_status_id');
    }
}
