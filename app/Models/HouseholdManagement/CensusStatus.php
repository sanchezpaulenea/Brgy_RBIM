<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CensusStatus extends Model
{
    public const COMPLETED = 1;

    public const CALLBACK = 2;

    public const REFUSED = 3;

    protected $table = 'census_status';

    protected $primaryKey = 'census_status_id';

    public $timestamps = false;

    protected $fillable = [
        'status_code',
        'status_name',
    ];

    /**
     * @return HasMany<HouseholdAssessment, $this>
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(HouseholdAssessment::class, 'census_status_id', 'census_status_id');
    }
}
