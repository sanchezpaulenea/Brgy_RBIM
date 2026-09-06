<?php

namespace App\Models\ResidentManagement\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolLvl extends Model
{
    protected $table = 'school_lvl';

    protected $primaryKey = 'school_lvl_id';

    public $timestamps = false;

    protected $fillable = [
        'school_lvl',
    ];

    public function getRouteKeyName(): string
    {
        return 'school_lvl_id';
    }

    /**
     * @return HasMany<Education, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'school_lvl_id', 'school_lvl_id');
    }
}
