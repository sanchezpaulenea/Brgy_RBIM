<?php

namespace App\Repositories\Interfaces\ResidentManagement\Economic;

use App\Models\ResidentManagement\Economic\Economic;
use Illuminate\Database\Eloquent\Collection;

interface EconomicRepositoryInterface
{
    /**
     * @return Collection<int, Economic>
     */
    public function listByResident(int $residentId): Collection;

    public function findById(int $economicId): ?Economic;

    public function findByResidentId(int $residentId): ?Economic;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Economic;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Economic $economic, array $attributes): Economic;
}
