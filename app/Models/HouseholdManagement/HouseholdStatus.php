<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HouseholdStatus extends Model
{
    public const ACTIVE = 1;

    public const INACTIVE = 2;

    public const ARCHIVE = 3;

    protected $table = 'household_status';

    protected $primaryKey = 'household_status_id';

    public $timestamps = false;

    protected $fillable = [
        'household_status',
    ];

    /**
     * @return HasMany<Household, $this>
     */
    public function households(): HasMany
    {
        return $this->hasMany(Household::class, 'household_status_id', 'household_status_id');
    }
}
