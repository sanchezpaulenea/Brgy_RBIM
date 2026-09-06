<?php

namespace App\Repositories\Interfaces\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\InfantHealth;
use Illuminate\Database\Eloquent\Collection;

interface InfantHealthRepositoryInterface
{
    /**
     * @return Collection<int, InfantHealth>
     */
    public function listByResident(int $residentId): Collection;

    public function findById(int $infantHealthId): ?InfantHealth;

    public function findByResidentId(int $residentId): ?InfantHealth;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): InfantHealth;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(InfantHealth $infantHealth, array $attributes): InfantHealth;
}
