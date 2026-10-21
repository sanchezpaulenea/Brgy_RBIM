<?php

namespace App\Models\HouseholdManagement;

use App\Models\Concerns\FindsOrCreatesLookupByLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Breed extends Model
{
    use FindsOrCreatesLookupByLabel;

    protected $table = 'breed';

    protected $primaryKey = 'breed_id';

    public $timestamps = false;

    protected $fillable = [
        'breed',
        'specie_id',
    ];

    public function belongsToOtherSpecie(int $specieId): bool
    {
        return $this->specie_id !== null && (int) $this->specie_id !== $specieId;
    }

    public function getRouteKeyName(): string
    {
        return 'breed_id';
    }

    /**
     * @return HasMany<PetCensus, $this>
     */
    public function pets(): HasMany
    {
        return $this->hasMany(PetCensus::class, 'breed_id', 'breed_id');
    }
}
