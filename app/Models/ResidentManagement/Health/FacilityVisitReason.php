<?php

namespace App\Models\ResidentManagement\Health;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilityVisitReason extends Model
{
    protected $table = 'facility_visit_reason';

    protected $primaryKey = 'facility_visit_reason_id';

    public $timestamps = false;

    protected $fillable = [
        'facility_visit_reason',
    ];

    public function getRouteKeyName(): string
    {
        return 'facility_visit_reason_id';
    }

    /**
     * @return HasMany<Health, $this>
     */
    public function healthRecords(): HasMany
    {
        return $this->hasMany(Health::class, 'facility_visit_reason_id', 'facility_visit_reason_id');
    }
}
