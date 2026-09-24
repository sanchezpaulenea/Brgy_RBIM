<?php

namespace App\Models\HouseholdManagement;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Household extends Model
{
    protected $table = 'household';

    protected $primaryKey = 'household_id';

    public $timestamps = false;

    protected $fillable = [
        'clan_id',
        'head_resident_id',
        'street_id',
        'number_of_house_story',
        'number_of_basement_level',
        'house_lot',
        'registration_date',
        'household_status_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'registration_date' => 'datetime',
        'number_of_house_story' => 'integer',
        'number_of_basement_level' => 'integer',
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
     * @return HasOne<HouseholdQuestions, $this>
     */
    public function questions(): HasOne
    {
        return $this->hasOne(HouseholdQuestions::class, 'household_id', 'household_id')
            ->latestOfMany('household_questions_id');
    }

    /**
     * @return HasMany<PetCensus, $this>
     */
    public function pets(): HasMany
    {
        return $this->hasMany(PetCensus::class, 'household_id', 'household_id');
    }
}
