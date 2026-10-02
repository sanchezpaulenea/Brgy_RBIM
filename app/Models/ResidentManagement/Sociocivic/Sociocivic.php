<?php

namespace App\Models\ResidentManagement\Sociocivic;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sociocivic extends Model
{
    protected $table = 'sociocivic';

    protected $primaryKey = 'sociocivic_id';

    public $timestamps = false;

    protected $fillable = [
        'solo_parent_status_id',
        'ncsc_rrn_id_number',
        'osca_id_number',
        'solo_parent_id_number',
        'registered_barangay_voter',
        'resident_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'sociocivic_id';
    }

    /**
     * No dedicated senior-citizen flag exists. An OSCA ID is the proxy.
     */
    protected function registeredSeniorViaOsca(): Attribute
    {
        return Attribute::get(function (): bool {
            $oscaId = $this->osca_id_number;

            if (! is_string($oscaId)) {
                return false;
            }

            return trim($oscaId) !== '';
        });
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
