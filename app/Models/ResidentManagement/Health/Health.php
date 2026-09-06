<?php

namespace App\Models\ResidentManagement\Health;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Health extends Model
{
    protected $table = 'health';

    protected $primaryKey = 'health_id';

    public $timestamps = false;

    protected $fillable = [
        'health_insurance_id',
        'facility_visited_past_12mos_id',
        'facility_visit_reason_id',
        'disability',
        'resident_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'health_id';
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }

    /**
     * @return BelongsTo<HealthInsurance, $this>
     */
    public function healthInsurance(): BelongsTo
    {
        return $this->belongsTo(HealthInsurance::class, 'health_insurance_id', 'health_insurance_id');
    }

    /**
     * @return BelongsTo<FacilityVisitedPast12Mos, $this>
     */
    public function facilityVisitedPast12Mos(): BelongsTo
    {
        return $this->belongsTo(
            FacilityVisitedPast12Mos::class,
            'facility_visited_past_12mos_id',
            'facility_visited_past_12mos_id'
        );
    }

    /**
     * @return BelongsTo<FacilityVisitReason, $this>
     */
    public function facilityVisitReason(): BelongsTo
    {
        return $this->belongsTo(
            FacilityVisitReason::class,
            'facility_visit_reason_id',
            'facility_visit_reason_id'
        );
    }

    /**
     * @return HasOne<WomenHealth, $this>
     */
    public function womenHealth(): HasOne
    {
        return $this->hasOne(WomenHealth::class, 'health_id', 'health_id');
    }
}
