<?php

namespace App\Models\AuditLog;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    public const CREATE = 1;

    public const UPDATE = 2;

    public const VIEW = 3;

    protected $table = 'action';

    protected $primaryKey = 'action_id';

    public $timestamps = false;

    protected $fillable = ['action'];
}
