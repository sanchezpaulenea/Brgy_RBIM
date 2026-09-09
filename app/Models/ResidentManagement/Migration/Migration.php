<?php

namespace App\Models\ResidentManagement\Migration;

use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\ResidentManagement\Demographic\ResidentType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Migration extends Model
{
    protected $table = 'migration';

    protected $primaryKey = 'migration_id';

    public $timestamps = false;

    protected $fillable = [
        'previous_residence_6mos_brgy',
        'previous_residence_6mos_city_municipality',
        'previous_residence_5yrs_brgy',
        'previous_residence_5yrs_city_municipality',
        'date_of_transfer_in_brgy',
        'resident_type_id',
        'reason_for_leaving_id',
        'will_return_to_previous_residence',
        'reason_for_transfer_id',
        'duration_of_stay',
        'resident_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_transfer_in_brgy' => 'date',
        'duration_of_stay' => 'date',
    ];

    /**
     * Keep null for non-migrants instead of coercing to false.
     *
     * @return Attribute<bool|null, bool|null>
     */
    protected function willReturnToPreviousResidence(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === null ? null : (bool) $value,
            set: fn ($value) => $value === null || $value === '' ? null : (int) (bool) $value,
        );
    }

    public function getRouteKeyName(): string
    {
        return 'migration_id';
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }

    /**
     * @return BelongsTo<ResidentType, $this>
     */
    public function residentType(): BelongsTo
    {
        return $this->belongsTo(ResidentType::class, 'resident_type_id', 'resident_type_id');
    }

    /**
     * @return BelongsTo<ReasonForLeaving, $this>
     */
    public function reasonForLeaving(): BelongsTo
    {
        return $this->belongsTo(ReasonForLeaving::class, 'reason_for_leaving_id', 'reason_for_leaving_id');
    }

    /**
     * @return BelongsTo<ReasonForTransfer, $this>
     */
    public function reasonForTransfer(): BelongsTo
    {
        return $this->belongsTo(ReasonForTransfer::class, 'reason_for_transfer_id', 'reason_for_transfer_id');
    }
}
