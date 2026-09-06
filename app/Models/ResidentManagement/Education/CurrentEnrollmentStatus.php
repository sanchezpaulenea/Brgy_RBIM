<?php

namespace App\Models\ResidentManagement\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CurrentEnrollmentStatus extends Model
{
    public const NOT_ENROLLED = 3;

    protected $table = 'current_enrollment_status';

    protected $primaryKey = 'current_enrollment_status_id';

    public $timestamps = false;

    protected $fillable = [
        'current_enrollement_status',
    ];

    public function getRouteKeyName(): string
    {
        return 'current_enrollment_status_id';
    }

    public function isNotEnrolled(): bool
    {
        if ((int) $this->current_enrollment_status_id === self::NOT_ENROLLED) {
            return true;
        }

        return strcasecmp(trim((string) $this->current_enrollement_status), 'No') === 0;
    }

    /**
     * @return HasMany<Education, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'current_enrollement_status_id', 'current_enrollment_status_id');
    }
}
