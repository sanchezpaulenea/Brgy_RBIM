<?php

namespace App\Models\ResidentManagement\Health;

use App\Models\Concerns\FindsOrCreatesLookupByLabel;
use App\Models\Concerns\ResolvesLookupSentinel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disability extends Model
{
    use FindsOrCreatesLookupByLabel;
    use ResolvesLookupSentinel;

    protected $table = 'disability';

    protected $primaryKey = 'disability_id';

    public $timestamps = false;

    protected $fillable = [
        'disability',
    ];

    public function getRouteKeyName(): string
    {
        return 'disability_id';
    }

    /**
     * @return HasMany<Health, $this>
     */
    public function healthRecords(): HasMany
    {
        return $this->hasMany(Health::class, 'disability_id', 'disability_id');
    }
}
