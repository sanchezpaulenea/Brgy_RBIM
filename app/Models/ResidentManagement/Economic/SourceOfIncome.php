<?php

namespace App\Models\ResidentManagement\Economic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceOfIncome extends Model
{
    protected $table = 'source_of_income';

    protected $primaryKey = 'source_of_income_id';

    public $timestamps = false;

    protected $fillable = [
        'source_of_income',
    ];

    public function getRouteKeyName(): string
    {
        return 'source_of_income_id';
    }

    /**
     * @return HasMany<Economic, $this>
     */
    public function economics(): HasMany
    {
        return $this->hasMany(Economic::class, 'source_of_income_id', 'source_of_income_id');
    }
}
