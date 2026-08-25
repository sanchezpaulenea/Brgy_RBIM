<?php

namespace App\Repositories\HouseholdManagement;

use App\Models\HouseholdManagement\Household;
use App\Models\HouseholdManagement\Street;
use App\Repositories\Interfaces\HouseholdManagement\StreetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StreetRepository implements StreetRepositoryInterface
{
    /**
     * @return Collection<int, Street>
     */
    public function all(): Collection
    {
        return Street::query()
            ->withCount('households')
            ->orderBy('street_name')
            ->get();
    }

    public function findById(int $streetId): ?Street
    {
        return Street::query()
            ->where('street_id', $streetId)
            ->first();
    }

    public function lockById(int $streetId): ?Street
    {
        return Street::query()
            ->where('street_id', $streetId)
            ->lockForUpdate()
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Street
    {
        return Street::create($attributes);
    }

    public function delete(Street $street): bool
    {
        return (bool) $street->delete();
    }

    public function isInUse(Street $street): bool
    {
        return Household::query()
            ->where('street_id', $street->getKey())
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Street $street): array
    {
        return [
            'id' => $street->street_id,
            'label' => $street->street_name,
            'street_id' => $street->street_id,
            'street_name' => $street->street_name,
            'in_use' => (int) ($street->households_count ?? 0) > 0,
        ];
    }
}
