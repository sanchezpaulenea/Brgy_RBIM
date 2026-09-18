<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CommonDiseaseCauseDeathInBrgy extends Model
{
    protected $table = 'common_disease_cause_death_in_brgy';

    protected $primaryKey = 'common_disease_id';

    public $timestamps = false;

    protected $fillable = [
        'common_disease',
    ];

    public function getRouteKeyName(): string
    {
        return 'common_disease_id';
    }

    /**
     * @return BelongsToMany<HouseholdQuestions, $this>
     */
    public function householdQuestions(): BelongsToMany
    {
        return $this->belongsToMany(
            HouseholdQuestions::class,
            'common_disease',
            'common_disease_id',
            'household_question_id',
        );
    }
}
