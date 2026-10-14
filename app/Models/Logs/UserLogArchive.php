<?php

namespace App\Models\Logs;

use App\Models\Authentication\LoginStatus;
use App\Models\UserManagement\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLogArchive extends Model
{
    protected $table = 'user_log_archive';

    protected $primaryKey = 'user_log_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'user_log_id',
        'user_id',
        'login_time',
        'logout_time',
        'login_status_id',
        'ip_address',
        'device',
        'archived_at',
        'archived_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
        'archived_at' => 'datetime',
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
