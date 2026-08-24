<?php

namespace App\Repositories\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use App\Repositories\Interfaces\HouseholdManagement\HouseholdRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class HouseholdRepository implements HouseholdRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'clan',
            'street',
            'status',
            'head.sex',
            'head.nationality',
            'head.religion',
            'head.ethnicity',
            'head.maritalStatus',
            'head.residentType',
            'head.status',
            'head.clan',
            'head.relationshipToHouseholdHead',
        ];
    }

    /**
     * @return Collection<int, Household>
     */
    public function all(): Collection
    {
        return Household::query()
            ->with($this->defaultRelations())
            ->orderByDesc('household_id')
            ->get();
    }

    public function findById(int $householdId): ?Household
    {
        return Household::query()
            ->with($this->defaultRelations())
            ->where('household_id', $householdId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Household
    {
        return Household::create($attributes);
    }

    public function updateHeadResident(Household $household, int $residentId): Household
    {
        $household->head_resident_id = $residentId;
        $household->save();

        return $household->fresh($this->defaultRelations()) ?? $household;
    }
}
