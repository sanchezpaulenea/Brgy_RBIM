<?php

namespace App\Models\Logs;

use App\Models\UserManagement\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLogArchive extends Model
{
    protected $table = 'audit_log_archive';

    protected $primaryKey = 'audit_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'audit_id',
        'user_id',
        'action_id',
        'record_id',
        'description',
        'old_value',
        'new_value',
        'performed_at',
        'target',
        'entity',
        'archived_at',
        'archived_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'performed_at' => 'datetime',
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
     * @return BelongsTo<Action, $this>
     */
    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class, 'action_id', 'action_id');
    }
}
