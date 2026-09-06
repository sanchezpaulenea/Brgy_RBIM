<?php

namespace App\Models\ResidentManagement\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HighestLvlOfEduc extends Model
{
    protected $table = 'highest_lvl_of_educ';

    protected $primaryKey = 'highest_lvl_of_educ_id';

    public $timestamps = false;

    protected $fillable = [
        'lvl_of_educ',
    ];

    public function getRouteKeyName(): string
    {
        return 'highest_lvl_of_educ_id';
    }

    /**
     * @return HasMany<Education, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'highest_lvl_of_educ_id', 'highest_lvl_of_educ_id');
    }
}
