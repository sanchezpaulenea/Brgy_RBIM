<?php

namespace App\Models\HouseholdManagement;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Household extends Model
{
    protected $table = 'household';

    protected $primaryKey = 'household_id';

    public $timestamps = false;

    protected $fillable = [
        'clan_id',
        'head_resident_id',
        'street_id',
        'house_lot',
        'block_num',
        'building_name',
        'unit_num',
        'registration_date',
        'household_status_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'registration_date' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'household_id';
    }

    /**
     * @return BelongsTo<Clan, $this>
     */
    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class, 'clan_id', 'clan_id');
    }

    /**
     * @return BelongsTo<Street, $this>
     */
    public function street(): BelongsTo
    {
        return $this->belongsTo(Street::class, 'street_id', 'street_id');
    }

    /**
     * @return BelongsTo<HouseholdStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(HouseholdStatus::class, 'household_status_id', 'household_status_id');
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'head_resident_id', 'resident_id');
    }

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'household_id', 'household_id');
    }

    /**
     * @return HasMany<HouseholdAssessment, $this>
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(HouseholdAssessment::class, 'household_id', 'household_id');
    }
}
