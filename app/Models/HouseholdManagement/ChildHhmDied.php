<?php

namespace App\Models\HouseholdManagement;

use App\Models\ResidentManagement\Demographic\Sex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildHhmDied extends Model
{
    protected $table = 'child_hhm_died';

    protected $primaryKey = 'child_hhm_died_id';

    public $timestamps = false;

    protected $fillable = [
        'age',
        'cause_of_death',
        'sex_id',
        'household_question_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'child_hhm_died_id';
    }

    /**
     * @return BelongsTo<HouseholdQuestions, $this>
     */
    public function householdQuestions(): BelongsTo
    {
        return $this->belongsTo(HouseholdQuestions::class, 'household_question_id', 'household_questions_id');
    }

    /**
     * @return BelongsTo<Sex, $this>
     */
    public function sex(): BelongsTo
    {
        return $this->belongsTo(Sex::class, 'sex_id', 'sex_id');
    }
}
