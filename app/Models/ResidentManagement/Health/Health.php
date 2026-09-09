<?php

namespace App\Models\ResidentManagement\Health;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Health extends Model
{
    /**
     * Sentinel stored when the resident has no disability / no PWD ID.
     *
     * health.pwd_id_number is INT NOT NULL with no DEFAULT, so NULL cannot
     * be persisted without a schema change. 0 = not applicable / no PWD ID.
     */
    public const PWD_ID_NOT_APPLICABLE = 0;

    protected $table = 'health';

    protected $primaryKey = 'health_id';

    public $timestamps = false;

    protected $fillable = [
        'health_insurance_id',
        'facility_visited_past_12mos_id',
        'facility_visit_reason_id',
        'disability',
        'pwd_id_number',
        'resident_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'health_id';
    }

    /**
     * True when disability is a real condition (not empty / "None").
     */
    public static function indicatesDisability(mixed $disability): bool
    {
        if (! is_string($disability)) {
            return false;
        }

        $value = Str::of($disability)->squish()->lower()->toString();

        if ($value === '') {
            return false;
        }

        return ! (bool) preg_match('/^(none|n\/a|n\.a\.?|na|not applicable)$/', $value);
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
