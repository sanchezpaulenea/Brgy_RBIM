<?php

namespace App\Models\ResidentManagement\Skill;

use App\Models\Concerns\FindsOrCreatesLookupByLabel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillsDevelopmentTraining extends Model
{
    use FindsOrCreatesLookupByLabel;

    protected $table = 'skills_development_training';

    protected $primaryKey = 'skills_development_training_id';

    public $timestamps = false;

    protected $fillable = [
        'skills_development_training',
    ];

    public function getRouteKeyName(): string
    {
        return 'skills_development_training_id';
    }

    /**
     * @return HasMany<SkillsDevelopment, $this>
     */
    public function skillsDevelopments(): HasMany
    {
        return $this->hasMany(SkillsDevelopment::class, 'skills_development_training_id', 'skills_development_training_id');
    }
}
