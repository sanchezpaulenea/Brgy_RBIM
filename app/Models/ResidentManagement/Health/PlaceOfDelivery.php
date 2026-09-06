<?php

namespace App\Models\ResidentManagement\Health;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlaceOfDelivery extends Model
{
    protected $table = 'place_of_delivery';

    protected $primaryKey = 'place_of_delivery_id';

    public $timestamps = false;

    protected $fillable = [
        'place_of_delivery',
    ];

    public function getRouteKeyName(): string
    {
        return 'place_of_delivery_id';
    }

    /**
     * @return HasMany<InfantHealth, $this>
     */
    public function infantHealthRecords(): HasMany
    {
        return $this->hasMany(InfantHealth::class, 'place_of_delivery_id', 'place_of_delivery_id');
    }
}
