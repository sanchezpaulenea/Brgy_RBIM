<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FemaleHhmDied extends Model
{
    protected $table = 'female_hhm_died';

    protected $primaryKey = 'female_hhm_died_id';

    public $timestamps = false;

    protected $fillable = [
        'age',
        'cause_of_death',
        'household_question_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'female_hhm_died_id';
    }

    /**
     * @return BelongsTo<HouseholdQuestions, $this>
     */
    public function householdQuestions(): BelongsTo
    {
        return $this->belongsTo(HouseholdQuestions::class, 'household_question_id', 'household_questions_id');
    }
}
