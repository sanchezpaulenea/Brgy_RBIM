<?php

namespace App\Models\BarangayPersonnel;

use App\Models\ResidentManagement\Demographic\Sex;
use App\Models\ResidentManagement\Demographic\Suffix;
use App\Models\UserManagement\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BarangayPersonnel extends Model
{
    protected $table = 'barangay_personnel';

    protected $primaryKey = 'personnel_id';

    public $timestamps = false;

    protected $fillable = [
        'position_id',
        'personnel_last_name',
        'personnel_first_name',
        'personnel_middle_name',
        'personnel_suffix_id',
        'personnel_status_id',
        'personnel_date_of_birth',
        'sex_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'personnel_date_of_birth' => 'date',
    ];

    public function getRouteKeyName(): string
    {
        return 'personnel_id';
    }

    /**
     * @return BelongsTo<PersonnelPosition, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(PersonnelPosition::class, 'position_id', 'position_id');
    }

    /**
     * @return BelongsTo<PersonnelStatus, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PersonnelStatus::class, 'personnel_status_id', 'personnel_status_id');
    }

    /**
     * @return BelongsTo<Sex, $this>
     */
    public function sex(): BelongsTo
    {
        return $this->belongsTo(Sex::class, 'sex_id', 'sex_id');
    }

    /**
     * @return BelongsTo<Suffix, $this>
     */
    public function nameSuffix(): BelongsTo
    {
        return $this->belongsTo(Suffix::class, 'personnel_suffix_id', 'suffix_id');
    }

    public function printedSuffix(): string
    {
        $this->loadMissing('nameSuffix');

        return $this->nameSuffix?->printed() ?? '';
    }

    /**
     * @return HasOne<User, $this>
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'personnel_id', 'personnel_id');
    }
}
