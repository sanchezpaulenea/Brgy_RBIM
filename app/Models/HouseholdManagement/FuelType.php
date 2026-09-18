<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FuelType extends Model
{
    protected $table = 'fuel_type';

    protected $primaryKey = 'fuel_type_id';

    public $timestamps = false;

    protected $fillable = [
        'fuel_type',
    ];

    public function getRouteKeyName(): string
    {
        return 'fuel_type_id';
    }

    /**
     * @return HasMany<HouseholdQuestions, $this>
     */
    public function lightingHouseholds(): HasMany
    {
        return $this->hasMany(HouseholdQuestions::class, 'fuel_type_for_lighting_id', 'fuel_type_id');
    }

    /**
     * @return HasMany<HouseholdQuestions, $this>
     */
    public function cookingHouseholds(): HasMany
    {
        return $this->hasMany(HouseholdQuestions::class, 'fuel_type_for_cooking_id', 'fuel_type_id');
    }
}
