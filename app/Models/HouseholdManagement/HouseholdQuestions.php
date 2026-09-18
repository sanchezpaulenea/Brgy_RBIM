<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HouseholdQuestions extends Model
{
    protected $table = 'household_questions';

    protected $primaryKey = 'household_questions_id';

    public $timestamps = false;

    protected $fillable = [
        'ownership_of_housing_unit_id',
        'ownership_of_lot_id',
        'fuel_type_for_lighting_id',
        'fuel_type_for_cooking_id',
        'main_source_drinking_water_id',
        'kitchen_garbage_disposal_id',
        'perform_garbage_seggragation',
        'toilet_facility_type_id',
        'type_of_building_house_id',
        'construction_material_outer_wall_id',
        'female_hhm_died_past_12mos',
        'child_hhm_died_past_12mos',
        'household_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'perform_garbage_seggragation' => 'boolean',
        'female_hhm_died_past_12mos' => 'boolean',
        'child_hhm_died_past_12mos' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'household_questions_id';
    }

    /**
     * @return BelongsTo<Household, $this>
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    /**
     * @return BelongsTo<OwnershipType, $this>
     */
    public function ownershipOfHousingUnit(): BelongsTo
    {
        return $this->belongsTo(OwnershipType::class, 'ownership_of_housing_unit_id', 'ownership_type_id');
    }

    /**
     * @return BelongsTo<OwnershipType, $this>
     */
    public function ownershipOfLot(): BelongsTo
    {
        return $this->belongsTo(OwnershipType::class, 'ownership_of_lot_id', 'ownership_type_id');
    }

    /**
     * @return BelongsTo<FuelType, $this>
     */
    public function fuelTypeForLighting(): BelongsTo
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_for_lighting_id', 'fuel_type_id');
    }

    /**
     * @return BelongsTo<FuelType, $this>
     */
    public function fuelTypeForCooking(): BelongsTo
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_for_cooking_id', 'fuel_type_id');
    }

    /**
     * @return BelongsTo<WaterSource, $this>
     */
    public function mainSourceDrinkingWater(): BelongsTo
    {
        return $this->belongsTo(WaterSource::class, 'main_source_drinking_water_id', 'water_source');
    }

    /**
     * @return BelongsTo<KitchenGarbageDisposal, $this>
     */
    public function kitchenGarbageDisposal(): BelongsTo
    {
        return $this->belongsTo(KitchenGarbageDisposal::class, 'kitchen_garbage_disposal_id', 'kitchen_garbage_disposal_id');
    }

    /**
     * @return BelongsTo<ToiletFacilityType, $this>
     */
    public function toiletFacilityType(): BelongsTo
    {
        return $this->belongsTo(ToiletFacilityType::class, 'toilet_facility_type_id', 'toilet_facility_type_id');
    }

    /**
     * @return BelongsTo<BuildingHouseType, $this>
     */
    public function typeOfBuildingHouse(): BelongsTo
    {
        return $this->belongsTo(BuildingHouseType::class, 'type_of_building_house_id', 'building_house_type_id');
    }

    /**
     * @return BelongsTo<ConstructionMaterialOuterWall, $this>
     */
    public function constructionMaterialOuterWall(): BelongsTo
    {
        return $this->belongsTo(ConstructionMaterialOuterWall::class, 'construction_material_outer_wall_id', 'construction_material_outer_wall_id');
    }

    /**
     * @return BelongsToMany<CommonDiseaseCauseDeathInBrgy, $this>
     */
    public function commonDiseases(): BelongsToMany
    {
        return $this->belongsToMany(
            CommonDiseaseCauseDeathInBrgy::class,
            'common_disease',
            'household_question_id',
            'common_disease_id',
        );
    }

    /**
     * @return BelongsToMany<PrimaryNeedOfBrgy, $this>
     */
    public function primaryNeeds(): BelongsToMany
    {
        return $this->belongsToMany(
            PrimaryNeedOfBrgy::class,
            'primary_need',
            'household_question_id',
            'primary_need_id',
        );
    }

    /**
     * @return HasOne<IntendToStay5yrsFromNow, $this>
     */
    public function intendToStay(): HasOne
    {
        return $this->hasOne(IntendToStay5yrsFromNow::class, 'household_question_id', 'household_questions_id');
    }
}
