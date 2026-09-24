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
    ];

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
