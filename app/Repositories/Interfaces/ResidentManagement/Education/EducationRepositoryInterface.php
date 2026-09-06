<?php

namespace App\Repositories\Interfaces\ResidentManagement\Education;

use App\Models\ResidentManagement\Education\Education;
use Illuminate\Database\Eloquent\Collection;

interface EducationRepositoryInterface
{
    /**
     * @return Collection<int, Education>
     */
    public function listByResident(int $residentId): Collection;

    public function findById(int $educationId): ?Education;

    public function findByResidentId(int $residentId): ?Education;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Education;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Education $education, array $attributes): Education;
}
