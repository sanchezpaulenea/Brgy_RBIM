<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Religion extends Model
{
    protected $table = 'religion';

    protected $primaryKey = 'religion_id';

    public $timestamps = false;

    protected $fillable = [
        'religion',
    ];

    public function getRouteKeyName(): string
    {
        return 'religion_id';
    }

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'religion_id', 'religion_id');
    }
}
