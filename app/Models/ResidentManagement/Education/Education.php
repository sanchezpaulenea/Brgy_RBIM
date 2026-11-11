<?php

namespace App\Models\ResidentManagement\Education;

use App\Models\Geographic\Barangay;
use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    protected $table = 'education';

    protected $primaryKey = 'education_id';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'resident_id',
        'highest_lvl_of_educ_id',
        'current_enrollement_status_id',
        'school_lvl_id',
        'place_of_school_brgy_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'education_id';
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }

    /**
     * @return BelongsTo<HighestLvlOfEduc, $this>
     */
    public function highestLvlOfEduc(): BelongsTo
    {
        return $this->belongsTo(HighestLvlOfEduc::class, 'highest_lvl_of_educ_id', 'highest_lvl_of_educ_id');
    }

    /**
     * @return BelongsTo<CurrentEnrollmentStatus, $this>
     */
    public function currentEnrollmentStatus(): BelongsTo
    {
        return $this->belongsTo(
            CurrentEnrollmentStatus::class,
            'current_enrollement_status_id',
            'current_enrollment_status_id'
        );
    }

    /**
     * @return BelongsTo<SchoolLvl, $this>
     */
    public function schoolLvl(): BelongsTo
    {
        return $this->belongsTo(SchoolLvl::class, 'school_lvl_id', 'school_lvl_id');
    }

    /**
     * @return BelongsTo<Barangay, $this>
     */
    public function schoolBarangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class, 'place_of_school_brgy_id', 'barangay_id');
    }
}
