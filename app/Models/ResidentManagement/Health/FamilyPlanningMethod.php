<?php

namespace App\Models\ResidentManagement\Health;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyPlanningMethod extends Model
{
    protected $table = 'family_planning_method';

    protected $primaryKey = 'family_planning_method_id';

    public $timestamps = false;

    protected $fillable = [
        'family_planning_method',
    ];

    public function getRouteKeyName(): string
    {
        return 'family_planning_method_id';
    }

    public function indicatesNone(): bool
    {
        return (bool) preg_match('/\bnone\b/i', (string) $this->family_planning_method);
    }

    /**
     * @return HasMany<WomenHealth, $this>
     */
    public function womenHealthRecords(): HasMany
    {
        return $this->hasMany(WomenHealth::class, 'family_planning_method_id', 'family_planning_method_id');
    }
}
