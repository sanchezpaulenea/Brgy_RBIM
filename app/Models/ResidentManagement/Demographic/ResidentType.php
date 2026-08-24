<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResidentType extends Model
{
    public const PERMANENT_RESIDENT = 1;

    public const MIGRANT = 2;

    public const TRANSIENT = 3;

    protected $table = 'resident_type';

    protected $primaryKey = 'resident_type_id';

    public $timestamps = false;

    protected $fillable = [
        'resident_type',
    ];

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'resident_type_id', 'resident_type_id');
    }
}
