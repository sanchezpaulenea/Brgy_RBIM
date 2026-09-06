<?php

namespace App\Models\ResidentManagement\Skill;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillsDevelopment extends Model
{
    protected $table = 'skills_development';

    protected $primaryKey = 'skills_development_id';

    public $timestamps = false;

    protected $fillable = [
        'skills_development_training',
        'skill_type_id',
        'resident_id',
    ];

    public function getRouteKeyName(): string
    {
        return 'skills_development_id';
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }

    /**
     * @return BelongsTo<SkillType, $this>
     */
    public function skillType(): BelongsTo
    {
        return $this->belongsTo(SkillType::class, 'skill_type_id', 'skill_type_id');
    }
}
