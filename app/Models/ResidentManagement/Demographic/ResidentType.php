<?php

namespace App\Models\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Migration\Migration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResidentType extends Model
{
    public const NON_MIGRANT = 1;

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
     * @return HasMany<Migration, $this>
     */
    public function migrations(): HasMany
    {
        return $this->hasMany(Migration::class, 'resident_type_id', 'resident_type_id');
    }
}
