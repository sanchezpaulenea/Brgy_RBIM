<?php

namespace App\Models\HouseholdManagement;

use App\Models\ResidentManagement\Demographic\Sex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetCensus extends Model
{
    protected $table = 'pet_census';

    protected $primaryKey = 'pet_census_id';

    public $timestamps = false;

    protected $fillable = [
        'household_id',
        'specie_id',
        'breed_id',
        'sex_id',
        'pet_date_of_birth',
        'is_spay_neuter',
        'rabies_vaccination_date',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'pet_date_of_birth' => 'date',
        'rabies_vaccination_date' => 'date',
        'is_spay_neuter' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'pet_census_id';
    }

    /**
     * @return BelongsTo<Household, $this>
     */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'household_id', 'household_id');
    }

    /**
     * @return BelongsTo<Specie, $this>
     */
    public function specie(): BelongsTo
    {
        return $this->belongsTo(Specie::class, 'specie_id', 'specie_id');
    }

    /**
     * @return BelongsTo<Breed, $this>
     */
    public function breed(): BelongsTo
    {
        return $this->belongsTo(Breed::class, 'breed_id', 'breed_id');
    }

    /**
     * @return BelongsTo<Sex, $this>
     */
    public function sex(): BelongsTo
    {
        return $this->belongsTo(Sex::class, 'sex_id', 'sex_id');
    }
}
