<?php

namespace App\Repositories\Interfaces\ResidentManagement\Skill;

use App\Models\ResidentManagement\Skill\SkillsDevelopment;

interface SkillRepositoryInterface
{
    public function findById(int $skillsDevelopmentId): ?SkillsDevelopment;

    public function findByResidentId(int $residentId): ?SkillsDevelopment;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): SkillsDevelopment;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(SkillsDevelopment $skillsDevelopment, array $attributes): SkillsDevelopment;
}
