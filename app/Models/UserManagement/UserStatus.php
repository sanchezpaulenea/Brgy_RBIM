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
