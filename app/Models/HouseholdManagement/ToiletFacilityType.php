<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToiletFacilityType extends Model
{
    protected $table = 'toilet_facility_type';

    protected $primaryKey = 'toilet_facility_type_id';

    public $timestamps = false;

    protected $fillable = [
        'toilet_facility_type',
    ];

    public function getRouteKeyName(): string
    {
        return 'toilet_facility_type_id';
    }

    /**
     * @return HasMany<HouseholdQuestions, $this>
     */
    public function householdQuestions(): HasMany
    {
        return $this->hasMany(HouseholdQuestions::class, 'toilet_facility_type_id', 'toilet_facility_type_id');
    }
}
