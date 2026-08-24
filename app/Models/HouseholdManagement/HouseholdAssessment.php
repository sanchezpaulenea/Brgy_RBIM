<?php

namespace App\Models\HouseholdManagement;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdAssessment extends Model
{
    protected $table = 'household_assessment';

    protected $primaryKey = 'assessment_id';

    public $timestamps = false;

    protected $fillable = [
        'household_id',
        'census_status_id',
        'visit_start',
        'visit_end',
        'next_visit_date',
        'interviewer_id',
        'supervisor_id',
        'encoder_id',
        'previous_assessment_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'visit_start' => 'datetime',
        'visit_end' => 'datetime',
        'next_visit_date' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'assessment_id';
    }

    /**
     * @return BelongsTo<Household, $this>
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    /**
     * @return BelongsTo<CensusStatus, $this>
     */
    public function censusStatus(): BelongsTo
    {
        return $this->belongsTo(CensusStatus::class, 'census_status_id', 'census_status_id');
    }

    /**
     * @return BelongsTo<BarangayPersonnel, $this>
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(BarangayPersonnel::class, 'interviewer_id', 'personnel_id');
    }

    /**
     * @return BelongsTo<BarangayPersonnel, $this>
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(BarangayPersonnel::class, 'supervisor_id', 'personnel_id');
    }

    /**
     * @return BelongsTo<BarangayPersonnel, $this>
     */
    public function encoder(): BelongsTo
    {
        return $this->belongsTo(BarangayPersonnel::class, 'encoder_id', 'personnel_id');
    }

    /**
     * @return BelongsTo<HouseholdAssessment, $this>
     */
    public function previousAssessment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_assessment_id', 'assessment_id');
    }
}
