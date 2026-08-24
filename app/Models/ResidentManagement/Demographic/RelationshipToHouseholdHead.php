<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelationshipToHouseholdHead extends Model
{
    public const HEAD = 1;

    protected $table = 'relationship_to_hh';

    protected $primaryKey = 'relationship_to_hh_id';

    public $timestamps = false;

    protected $fillable = [
        'relationship_to_hh',
    ];

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'relationship_to_hh_id', 'relationship_to_hh_id');
    }
}
