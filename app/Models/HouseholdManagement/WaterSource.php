<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaterSource extends Model
{
    protected $table = 'water_source';

    protected $primaryKey = 'water_source_id';

    public $timestamps = false;

    protected $fillable = [
        'water_source',
    ];

    public function getRouteKeyName(): string
    {
        return 'water_source_id';
    }

    /**
     * @return HasMany<HouseholdQuestions, $this>
     */
    public function householdQuestions(): HasMany
    {
        return $this->hasMany(HouseholdQuestions::class, 'main_source_drinking_water_id', 'water_source_id');
    }
}
