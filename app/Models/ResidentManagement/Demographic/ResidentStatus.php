<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResidentStatus extends Model
{
    public const ACTIVE = 1;

    public const MOVED_OUT = 2;

    public const DECEASED = 3;

    public const ARCHIVE = 4;

    protected $table = 'resident_status';

    protected $primaryKey = 'resident_status_id';

    public $timestamps = false;

    protected $fillable = [
        'resident_status',
    ];

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'resident_status_id', 'resident_status_id');
    }
}
