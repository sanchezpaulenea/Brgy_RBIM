<?php

namespace App\Repositories\Interfaces\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\Health;
use Illuminate\Database\Eloquent\Collection;

interface HealthRepositoryInterface
{
    /**
     * @return Collection<int, Health>
     */
    public function listByResident(int $residentId): Collection;

    public function findById(int $healthId): ?Health;

    public function findByResidentId(int $residentId): ?Health;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Health;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Health $health, array $attributes): Health;
}
