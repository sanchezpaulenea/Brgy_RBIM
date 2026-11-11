<?php

namespace App\Models\Geographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Province extends Model
{
    protected $table = 'province';

    protected $primaryKey = 'province_id';

    public $timestamps = false;

    protected $fillable = [
        'country_id',
        'province_name',
    ];

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'country_id');
    }
}
