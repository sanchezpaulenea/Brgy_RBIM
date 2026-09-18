<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConstructionMaterialOuterWall extends Model
{
    protected $table = 'construction_material_outer_wall';

    protected $primaryKey = 'construction_material_outer_wall_id';

    public $timestamps = false;

    protected $fillable = [
        'construction_material_outer_wall',
    ];

    public function getRouteKeyName(): string
    {
        return 'construction_material_outer_wall_id';
    }

    /**
     * @return HasMany<HouseholdQuestions, $this>
     */
    public function householdQuestions(): HasMany
    {
        return $this->hasMany(HouseholdQuestions::class, 'construction_material_outer_wall_id', 'construction_material_outer_wall_id');
    }
}
