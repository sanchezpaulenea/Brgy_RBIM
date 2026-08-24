<?php

namespace App\Repositories\Interfaces\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Collection;

interface ResidentRepositoryInterface
{
    /**
     * @return Collection<int, Resident>
     */
    public function all(): Collection;

    public function findById(int $residentId): ?Resident;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Resident;
}
