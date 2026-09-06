<?php

namespace App\Models\ResidentManagement\Economic;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Economic extends Model
{
    protected $table = 'economic';

    protected $primaryKey = 'economic_id';

    public $timestamps = false;

    protected $fillable = [
        'monthly_income',
        'source_of_income_id',
        'status_of_work_business_id',
        'place_of_work_business',
        'resident_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'economic_id';
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }

    /**
     * @return BelongsTo<SourceOfIncome, $this>
     */
    public function sourceOfIncome(): BelongsTo
    {
        return $this->belongsTo(SourceOfIncome::class, 'source_of_income_id', 'source_of_income_id');
    }

    /**
     * @return BelongsTo<StatusOfWorkBusiness, $this>
     */
    public function statusOfWorkBusiness(): BelongsTo
    {
        return $this->belongsTo(
            StatusOfWorkBusiness::class,
            'status_of_work_business_id',
            'status_of_work_business_id'
        );
    }
}
