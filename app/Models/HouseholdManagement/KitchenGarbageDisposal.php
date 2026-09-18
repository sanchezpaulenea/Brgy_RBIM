<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitchenGarbageDisposal extends Model
{
    protected $table = 'kitchen_garbage_disposal';

    protected $primaryKey = 'kitchen_garbage_disposal_id';

    public $timestamps = false;

    protected $fillable = [
        'kitchen_garbage_disposal',
    ];

    public function getRouteKeyName(): string
    {
        return 'kitchen_garbage_disposal_id';
    }

    /**
     * @return HasMany<HouseholdQuestions, $this>
     */
    public function householdQuestions(): HasMany
    {
        return $this->hasMany(HouseholdQuestions::class, 'kitchen_garbage_disposal_id', 'kitchen_garbage_disposal_id');
    }
}
