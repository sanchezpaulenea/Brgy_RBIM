<?php

namespace App\Models\BarangayPersonnel;

use Illuminate\Database\Eloquent\Model;

class PersonnelStatus extends Model
{
    protected $table = 'personnel_status';

    protected $primaryKey = 'personnel_status_id';

    public $timestamps = false;

    protected $fillable = [
        'personnel_status',
    ];
}
