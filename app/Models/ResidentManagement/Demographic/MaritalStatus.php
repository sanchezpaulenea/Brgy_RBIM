<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaritalStatus extends Model
{
    protected $table = 'marital_status';

    protected $primaryKey = 'marital_status_id';

    public $timestamps = false;

    protected $fillable = [
        'marital_status',
    ];

    /**
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'marital_status_id', 'marital_status_id');
    }
}
