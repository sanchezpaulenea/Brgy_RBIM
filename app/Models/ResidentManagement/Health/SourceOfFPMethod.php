<?php

namespace App\Models\ResidentManagement\Health;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceOfFPMethod extends Model
{
    protected $table = 'source_of_fp_method';

    protected $primaryKey = 'source_of_fp_method_id';

    public $timestamps = false;

    protected $fillable = [
        'source_of_fp_method',
    ];

    public function getRouteKeyName(): string
    {
        return 'source_of_fp_method_id';
    }

    /**
     * @return HasMany<WomenHealth, $this>
     */
    public function womenHealthRecords(): HasMany
    {
        return $this->hasMany(WomenHealth::class, 'source_of_fp_method_id', 'source_of_fp_method_id');
    }
}
