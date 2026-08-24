<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ethnicity extends Model
{
    protected $table = 'ethnicity';

    protected $primaryKey = 'ethnicity_id';

    public $timestamps = false;

    protected $fillable = [
        'ethnicity',
    ];

    public function getRouteKeyName(): string
    {
        return 'ethnicity_id';
    }

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'ethnicity_id', 'ethnicity_id');
    }
}
