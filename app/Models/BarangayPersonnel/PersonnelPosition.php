<?php

namespace App\Models\BarangayPersonnel;

use Illuminate\Database\Eloquent\Model;

class PersonnelPosition extends Model
{
    protected $table = 'personnel_position';

    protected $primaryKey = 'position_id';

    public $timestamps = false;

    protected $fillable = [
        'position_name',
    ];
}
