<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetStatus extends Model
{
    public const ACTIVE = 1;

    public const DISEASED = 2;

    protected $table = 'pet_status';

    protected $primaryKey = 'pet_status_id';

    public $timestamps = false;

    protected $fillable = [
        'pet_status',
    ];

    /**
     * @return HasMany<PetCensus, $this>
     */
    public function pets(): HasMany
    {
        return $this->hasMany(PetCensus::class, 'pet_status_id', 'pet_status_id');
    }
}
