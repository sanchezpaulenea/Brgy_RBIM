<?php

namespace App\Models\AuditLog;

use App\Models\UserManagement\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = 'audit_log';

    protected $primaryKey = 'audit_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action_id',
        'record_id',
        'description',
        'old_value',
        'new_value',
        'performed_at',
        'target',
        'entity',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'performed_at' => 'datetime',
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
