<?php

namespace App\Models\BarangayPersonnel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelPosition extends Model
{
    protected $table = 'personnel_position';

    protected $primaryKey = 'position_id';

    public $timestamps = false;

    protected $fillable = [
        'position_name',
    ];

    /**
     * @return HasMany<BarangayPersonnel, $this>
     */
    public function personnel(): HasMany
    {
        return $this->hasMany(BarangayPersonnel::class, 'position_id', 'position_id');
    }
}
