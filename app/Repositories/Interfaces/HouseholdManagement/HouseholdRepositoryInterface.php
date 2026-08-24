<?php

namespace App\Repositories\Interfaces\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use Illuminate\Database\Eloquent\Collection;

interface HouseholdRepositoryInterface
{
    /**
     * @return Collection<int, Household>
     */
    public function all(): Collection;

    public function findById(int $householdId): ?Household;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Household;

    public function updateHeadResident(Household $household, int $residentId): Household;
}
