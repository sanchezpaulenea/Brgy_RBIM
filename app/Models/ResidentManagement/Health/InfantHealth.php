<?php

namespace App\Models\ResidentManagement\Health;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfantHealth extends Model
{
    protected $table = 'infant_health';

    protected $primaryKey = 'infant_health_id';

    public $timestamps = false;

    protected $fillable = [
        'place_of_delivery_id',
        'birth_attendant_id',
        'immunization',
        'resident_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'infant_health_id';
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }

    /**
     * @return BelongsTo<PlaceOfDelivery, $this>
     */
    public function placeOfDelivery(): BelongsTo
    {
        return $this->belongsTo(PlaceOfDelivery::class, 'place_of_delivery_id', 'place_of_delivery_id');
    }

    /**
     * @return BelongsTo<BirthAttendant, $this>
     */
    public function birthAttendant(): BelongsTo
    {
        return $this->belongsTo(BirthAttendant::class, 'birth_attendant_id', 'birth_attendant_id');
    }
}
