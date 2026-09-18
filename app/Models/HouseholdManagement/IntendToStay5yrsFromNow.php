<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntendToStay5yrsFromNow extends Model
{
    protected $table = 'intend_to_stay_5yrs_from_now';

    protected $primaryKey = 'intend_to_stay_id';

    public $timestamps = false;

    protected $fillable = [
        'intend_to_stay_brgy',
        'intend_to_stay_municipality',
        'intend_to_stay_province',
        'household_question_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'intend_to_stay_id';
    }

    /**
     * @return BelongsTo<HouseholdQuestions, $this>
     */
    public function householdQuestions(): BelongsTo
    {
        return $this->belongsTo(HouseholdQuestions::class, 'household_question_id', 'household_questions_id');
    }
}
