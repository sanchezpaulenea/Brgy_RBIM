<?php

namespace App\Models\ResidentManagement\Health;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BirthAttendant extends Model
{
    protected $table = 'birth_attendant';

    protected $primaryKey = 'birth_attendant_id';

    public $timestamps = false;

    protected $fillable = [
        'birth_attendant',
    ];

    public function getRouteKeyName(): string
    {
        return 'birth_attendant_id';
    }

    /**
     * @return HasMany<InfantHealth, $this>
     */
    public function infantHealthRecords(): HasMany
    {
        return $this->hasMany(InfantHealth::class, 'birth_attendant_id', 'birth_attendant_id');
    }
}
