<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sex extends Model
{
    public const MALE = 1;

    public const FEMALE = 2;

    protected $table = 'sex';

    protected $primaryKey = 'sex_id';

    public $timestamps = false;

    protected $fillable = [
        'sex',
    ];

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'sex_id', 'sex_id');
    }
}
