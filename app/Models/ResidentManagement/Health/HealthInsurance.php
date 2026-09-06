<?php

namespace App\Models\ResidentManagement\Health;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HealthInsurance extends Model
{
    protected $table = 'health_insurance';

    protected $primaryKey = 'health_insurance_id';

    public $timestamps = false;

    protected $fillable = [
        'health_insurance',
    ];

    public function getRouteKeyName(): string
    {
        return 'health_insurance_id';
    }

    /**
     * @return HasMany<Health, $this>
     */
    public function healthRecords(): HasMany
    {
        return $this->hasMany(Health::class, 'health_insurance_id', 'health_insurance_id');
    }
}
