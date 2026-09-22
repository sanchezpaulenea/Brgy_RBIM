<?php

namespace App\Models\ResidentManagement\Economic;

use App\Models\Concerns\ResolvesLookupSentinel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusOfWorkBusiness extends Model
{
    use ResolvesLookupSentinel;

    protected $table = 'status_of_work_business';

    protected $primaryKey = 'status_of_work_business_id';

    public $timestamps = false;

    protected $fillable = [
        'status_of_work_business',
    ];

    public function getRouteKeyName(): string
    {
        return 'status_of_work_business_id';
    }

    /**
     * @return HasMany<Economic, $this>
     */
    public function economics(): HasMany
    {
        return $this->hasMany(Economic::class, 'status_of_work_business_id', 'status_of_work_business_id');
    }
}
