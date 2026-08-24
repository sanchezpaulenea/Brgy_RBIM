<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nationality extends Model
{
    protected $table = 'nationality';

    protected $primaryKey = 'nationality_id';

    public $timestamps = false;

    protected $fillable = [
        'nationality',
    ];

    public function getRouteKeyName(): string
    {
        return 'nationality_id';
    }

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'nationality_id', 'nationality_id');
    }
}
