<?php

namespace App\Models\ResidentManagement\Health;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilityVisitedPast12Mos extends Model
{
    protected $table = 'facility_visited_past_12mos';

    protected $primaryKey = 'facility_visited_past_12mos_id';

    public $timestamps = false;

    protected $fillable = [
        'facility_visited_past_12mos',
    ];

    public function getRouteKeyName(): string
    {
        return 'facility_visited_past_12mos_id';
    }

    /**
     * @return HasMany<Health, $this>
     */
    public function healthRecords(): HasMany
    {
        return $this->hasMany(Health::class, 'facility_visited_past_12mos_id', 'facility_visited_past_12mos_id');
    }
}
