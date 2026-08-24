<?php

namespace App\Models\HouseholdManagement;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clan extends Model
{
    protected $table = 'clan';

    protected $primaryKey = 'clan_id';

    public $timestamps = false;

    protected $fillable = [
        'clan_name',
    ];

    public function getRouteKeyName(): string
    {
        return 'clan_id';
    }

    /**
     * @return HasMany<Household, $this>
     */
    public function households(): HasMany
    {
        return $this->hasMany(Household::class, 'clan_id', 'clan_id');
    }

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'clan_id', 'clan_id');
    }
}
