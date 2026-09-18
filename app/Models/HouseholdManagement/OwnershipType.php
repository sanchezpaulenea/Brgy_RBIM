<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OwnershipType extends Model
{
    protected $table = 'ownership_type';

    protected $primaryKey = 'ownership_type_id';

    public $timestamps = false;

    protected $fillable = [
        'ownership_type',
    ];

    public function getRouteKeyName(): string
    {
        return 'ownership_type_id';
    }

    /**
     * @return HasMany<HouseholdQuestions, $this>
     */
    public function housingUnits(): HasMany
    {
        return $this->hasMany(HouseholdQuestions::class, 'ownership_of_housing_unit_id', 'ownership_type_id');
    }

    /**
     * @return HasMany<HouseholdQuestions, $this>
     */
    public function lots(): HasMany
    {
        return $this->hasMany(HouseholdQuestions::class, 'ownership_of_lot_id', 'ownership_type_id');
    }
}
