<?php

namespace App\Repositories\BarangayPersonnel;

use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Repositories\Interfaces\BarangayPersonnel\PersonnelPositionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonnelPositionRepository implements PersonnelPositionRepositoryInterface
{
    /**
     * @return Collection<int, PersonnelPosition>
     */
    public function all(): Collection
    {
        return PersonnelPosition::query()
            ->with(['personnel' => fn ($query) => $query->where('personnel_status_id', 1)])
            ->orderBy('position_name')
            ->get();
    }

    public function findById(int $positionId): ?PersonnelPosition
    {
        return PersonnelPosition::query()
            ->where('position_id', $positionId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PersonnelPosition
    {
        return PersonnelPosition::create($attributes);
    }

    public function delete(PersonnelPosition $position): bool
    {
        return (bool) $position->delete();
    }

    public function isInUse(PersonnelPosition $position): bool
    {
        return $position->personnel()->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(PersonnelPosition $position): array
    {
        $holder = $position->personnel->first();

        return [
            'id' => $position->position_id,
            'label' => $position->position_name,
            'occupied' => $holder !== null,
            'occupied_by_personnel_id' => $holder?->personnel_id,
        ];
    }
}
