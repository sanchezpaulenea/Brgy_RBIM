<?php

namespace App\Models\ResidentManagement\Sociocivic;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sociocivic extends Model
{
    protected $table = 'sociocivic';

    protected $primaryKey = 'sociocivic_id';

    public $timestamps = false;

    protected $fillable = [
        'solo_parent_status_id',
        'registered_sen_citizen',
        'registered_barangay_voter',
        'resident_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'registered_sen_citizen' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'sociocivic_id';
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }

    /**
     * @return BelongsTo<SoloParentStatus, $this>
     */
    public function soloParentStatus(): BelongsTo
    {
        return $this->belongsTo(SoloParentStatus::class, 'solo_parent_status_id', 'solo_parent_status_id');
    }
}
