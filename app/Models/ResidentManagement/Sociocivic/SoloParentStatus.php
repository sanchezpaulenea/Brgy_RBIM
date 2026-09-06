<?php

namespace App\Models\ResidentManagement\Sociocivic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoloParentStatus extends Model
{
    public const NON_SOLO_PARENT = 2;

    protected $table = 'solo_parent_status';

    protected $primaryKey = 'solo_parent_status_id';

    public $timestamps = false;

    protected $fillable = [
        'solo_parent_status',
    ];

    public function getRouteKeyName(): string
    {
        return 'solo_parent_status_id';
    }

    /**
     * @return HasMany<Sociocivic, $this>
     */
    public function sociocivics(): HasMany
    {
        return $this->hasMany(Sociocivic::class, 'solo_parent_status_id', 'solo_parent_status_id');
    }
}
