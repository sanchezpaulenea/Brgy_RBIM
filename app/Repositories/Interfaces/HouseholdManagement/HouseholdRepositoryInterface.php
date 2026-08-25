<?php

namespace App\Repositories\Interfaces\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use Illuminate\Database\Eloquent\Collection;

interface HouseholdRepositoryInterface
{
    /**
     * @param  array{street_id?: int, household_status_id?: int}  $filters
     * @return Collection<int, Household>
     */
    public function list(array $filters = []): Collection;

    public function findById(int $householdId, bool $withResidents = false): ?Household;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Household;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Household $household, array $attributes): Household;

    public function updateHeadResident(Household $household, int $residentId): Household;
}
