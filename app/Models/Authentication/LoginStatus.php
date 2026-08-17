<?php

namespace App\Models\Authentication;

use Illuminate\Database\Eloquent\Model;

class LoginStatus extends Model
{
    protected $table = 'login_status';

    protected $primaryKey = 'login_status_id';

    public $timestamps = false;

    protected $fillable = ['login_status'];

    /** Seeded login_status_id constants for use throughout the application. */
    public const SUCCESS = 1;

    public const INVALID_PASSWORD = 2;

    public const INVALID_USERNAME = 3;

    public const INVALID_CREDENTIALS = 4;

    public const ACCOUNT_LOCKED = 5;
}
