<?php

namespace App\Models\BarangayPersonnel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelStatus extends Model
{
    protected $table = 'personnel_status';

    protected $primaryKey = 'personnel_status_id';

    public $timestamps = false;

    protected $fillable = [
        'personnel_status',
    ];

    /** Seeded personnel_status_id constants for use throughout the application. */
    public const ACTIVE = 1;

    /**
     * @return HasMany<BarangayPersonnel, $this>
     */
    public function personnel(): HasMany
    {
        return $this->hasMany(BarangayPersonnel::class, 'personnel_status_id', 'personnel_status_id');
    }
}
