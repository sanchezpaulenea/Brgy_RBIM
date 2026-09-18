<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuildingHouseType extends Model
{
    protected $table = 'building_house_type';

    protected $primaryKey = 'building_house_type_id';

    public $timestamps = false;

    protected $fillable = [
        'building_house_type',
    ];

    public function getRouteKeyName(): string
    {
        return 'building_house_type_id';
    }

    /**
     * @return HasMany<HouseholdQuestions, $this>
     */
    public function householdQuestions(): HasMany
    {
        return $this->hasMany(HouseholdQuestions::class, 'type_of_building_house_id', 'building_house_type_id');
    }
}
