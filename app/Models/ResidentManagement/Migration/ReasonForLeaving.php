<?php

namespace App\Models\ResidentManagement\Migration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReasonForLeaving extends Model
{
    protected $table = 'reason_for_leaving';

    protected $primaryKey = 'reason_for_leaving_id';

    public $timestamps = false;

    protected $fillable = [
        'reason_for_leaving',
    ];

    public function getRouteKeyName(): string
    {
        return 'reason_for_leaving_id';
    }

    /**
     * @return HasMany<Migration, $this>
     */
    public function migrations(): HasMany
    {
        return $this->hasMany(Migration::class, 'reason_for_leaving_id', 'reason_for_leaving_id');
    }
}
