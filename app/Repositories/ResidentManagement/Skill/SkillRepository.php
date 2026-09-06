<?php

namespace App\Repositories\ResidentManagement\Skill;

use App\Models\ResidentManagement\Skill\SkillsDevelopment;
use App\Repositories\Interfaces\ResidentManagement\Skill\SkillRepositoryInterface;

class SkillRepository implements SkillRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return ['skillType'];
    }

    public function findById(int $skillsDevelopmentId): ?SkillsDevelopment
    {
        return SkillsDevelopment::query()
            ->with($this->defaultRelations())
            ->where('skills_development_id', $skillsDevelopmentId)
            ->first();
    }

    public function findByResidentId(int $residentId): ?SkillsDevelopment
    {
        return SkillsDevelopment::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SkillsDevelopment
    {
        $skillsDevelopment = SkillsDevelopment::create($attributes);

        return $skillsDevelopment->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SkillsDevelopment $skillsDevelopment, array $attributes): SkillsDevelopment
    {
        $skillsDevelopment->fill($attributes);
        $skillsDevelopment->save();

        return $skillsDevelopment->fresh($this->defaultRelations()) ?? $skillsDevelopment;
    }
}
