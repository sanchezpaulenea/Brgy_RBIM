<?php

namespace App\Models\ResidentManagement\Skill;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillType extends Model
{
    protected $table = 'skill_type';

    protected $primaryKey = 'skill_type_id';

    public $timestamps = false;

    protected $fillable = [
        'skill_type',
    ];

    public function getRouteKeyName(): string
    {
        return 'skill_type_id';
    }

    /**
     * @return HasMany<SkillsDevelopment, $this>
     */
    public function skillsDevelopments(): HasMany
    {
        return $this->hasMany(SkillsDevelopment::class, 'skill_type_id', 'skill_type_id');
    }
}
