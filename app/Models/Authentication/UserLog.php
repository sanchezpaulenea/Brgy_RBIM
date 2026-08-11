<?php

namespace App\Models\Authentication;

use App\Models\UserManagement\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLog extends Model
{
    protected $table = 'user_log';

    protected $primaryKey = 'user_log_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'login_time',
        'logout_time',
        'login_status_id',
        'ip_address',
        'device',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * @return BelongsTo<LoginStatus, $this>
     */
    public function loginStatus(): BelongsTo
    {
        return $this->belongsTo(LoginStatus::class, 'login_status_id', 'login_status_id');
    }
}
