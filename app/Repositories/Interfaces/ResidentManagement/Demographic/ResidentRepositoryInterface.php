<?php

namespace App\Repositories\Interfaces\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Collection;

interface ResidentRepositoryInterface
{
    /**
     * @param  array{household_id?: int, resident_type_id?: int, resident_status_id?: int}  $filters
     * @return Collection<int, Resident>
     */
    public function list(array $filters = []): Collection;

    public function findById(int $residentId): ?Resident;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Resident;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Resident $resident, array $attributes): Resident;
}
