<?php

namespace App\Models\ResidentManagement\Migration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReasonForTransfer extends Model
{
    protected $table = 'reason_for_transfer';

    protected $primaryKey = 'reason_for_transfer_id';

    public $timestamps = false;

    protected $fillable = [
        'reason_for_transfer',
    ];

    public function getRouteKeyName(): string
    {
        return 'reason_for_transfer_id';
    }

    /**
     * @return HasMany<Migration, $this>
     */
    public function migrations(): HasMany
    {
        return $this->hasMany(Migration::class, 'reason_for_transfer_id', 'reason_for_transfer_id');
    }
}
