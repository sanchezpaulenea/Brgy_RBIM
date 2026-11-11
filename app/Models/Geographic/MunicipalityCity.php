<?php

namespace App\Models\Geographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MunicipalityCity extends Model
{
    protected $table = 'municipality_city';

    protected $primaryKey = 'municipality_city_id';

    public $timestamps = false;

    protected $fillable = [
        'province_id',
        'municipality_city_name',
    ];

    /**
     * @return BelongsTo<Province, $this>
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'province_id');
    }
}
