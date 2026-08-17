<?php

namespace App\Models\Audit;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    protected $table = 'action';

    protected $primaryKey = 'action_id';

    public $timestamps = false;

    protected $fillable = [
        'action',
    ];
}
