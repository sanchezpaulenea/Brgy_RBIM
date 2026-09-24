<?php

namespace App\Models\HouseholdManagement;

use App\Models\Concerns\FindsOrCreatesLookupByLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specie extends Model
{
    use FindsOrCreatesLookupByLabel;

    protected $table = 'specie';

    protected $primaryKey = 'specie_id';

    public $timestamps = false;

    protected $fillable = [
        'specie',
    ];

    public function getRouteKeyName(): string
    {
        return 'specie_id';
    }

    /**
     * @return HasMany<PetCensus, $this>
     */
    public function pets(): HasMany
    {
        return $this->hasMany(PetCensus::class, 'specie_id', 'specie_id');
    }
}
