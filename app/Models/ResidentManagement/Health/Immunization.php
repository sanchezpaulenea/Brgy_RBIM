<?php

namespace App\Models\ResidentManagement\Health;

use App\Models\Concerns\FindsOrCreatesLookupByLabel;
use App\Models\Concerns\ResolvesLookupSentinel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Immunization extends Model
{
    use FindsOrCreatesLookupByLabel;
    use ResolvesLookupSentinel;

    protected $table = 'immunization';

    protected $primaryKey = 'immunization_id';

    public $timestamps = false;

    protected $fillable = [
        'immunization',
    ];

    public function getRouteKeyName(): string
    {
        return 'immunization_id';
    }

    /**
     * @return HasMany<InfantHealth, $this>
     */
    public function infantHealthRecords(): HasMany
    {
        return $this->hasMany(InfantHealth::class, 'immunization_id', 'immunization_id');
    }
}
