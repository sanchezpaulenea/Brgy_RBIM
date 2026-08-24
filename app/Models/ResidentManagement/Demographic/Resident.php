<?php

namespace App\Models\ResidentManagement\Demographic;

use App\Models\HouseholdManagement\Clan;
use App\Models\HouseholdManagement\Household;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resident extends Model
{
    protected $table = 'resident';

    protected $primaryKey = 'resident_id';

    public $timestamps = false;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'relationship_to_hh_id',
        'sex_id',
        'date_of_birth',
        'birth_city_municipality',
        'birth_province',
        'birth_country',
        'nationality_id',
        'religion_id',
        'ethnicity_id',
        'marital_status_id',
        'resident_type_id',
        'clan_id',
        'resident_status_id',
        'household_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'resident_id';
    }

    /**
     * @return BelongsTo<Household, $this>
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    /**
     * @return BelongsTo<Clan, $this>
     */
    public function clan(): BelongsTo
    {
        return $this->belongsTo(Clan::class, 'clan_id', 'clan_id');
    }

    /**
     * @return BelongsTo<RelationshipToHouseholdHead, $this>
     */
    public function relationshipToHouseholdHead(): BelongsTo
    {
        return $this->belongsTo(
            RelationshipToHouseholdHead::class,
            'relationship_to_hh_id',
            'relationship_to_hh_id'
        );
    }

    /**
     * @return BelongsTo<Sex, $this>
     */
    public function sex(): BelongsTo
    {
        return $this->belongsTo(Sex::class, 'sex_id', 'sex_id');
    }

    /**
     * @return BelongsTo<Nationality, $this>
     */
    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'nationality_id', 'nationality_id');
    }

    /**
     * @return BelongsTo<Religion, $this>
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class, 'religion_id', 'religion_id');
    }

    /**
     * @return BelongsTo<Ethnicity, $this>
     */
    public function ethnicity(): BelongsTo
    {
        return $this->belongsTo(Ethnicity::class, 'ethnicity_id', 'ethnicity_id');
    }

    /**
     * @return BelongsTo<MaritalStatus, $this>
     */
    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class, 'marital_status_id', 'marital_status_id');
    }

    /**
     * @return BelongsTo<ResidentType, $this>
     */
    public function residentType(): BelongsTo
    {
        return $this->belongsTo(ResidentType::class, 'resident_type_id', 'resident_type_id');
    }

    /**
     * @return BelongsTo<ResidentStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ResidentStatus::class, 'resident_status_id', 'resident_status_id');
    }
}
