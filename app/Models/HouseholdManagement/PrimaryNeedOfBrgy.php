<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PrimaryNeedOfBrgy extends Model
{
    protected $table = 'primary_need_of_brgy';

    protected $primaryKey = 'primary_need_id';

    public $timestamps = false;

    protected $fillable = [
        'primary_need',
    ];

    public function getRouteKeyName(): string
    {
        return 'primary_need_id';
    }

    /**
     * @return BelongsToMany<HouseholdQuestions, $this>
     */
    public function householdQuestions(): BelongsToMany
    {
        return $this->belongsToMany(
            HouseholdQuestions::class,
            'primary_need',
            'primary_need_id',
            'household_question_id',
        );
    }
}
