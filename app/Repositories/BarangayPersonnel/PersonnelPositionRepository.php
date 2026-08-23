<?php

namespace App\Repositories\BarangayPersonnel;

use App\Models\BarangayPersonnel\BarangayPersonnel;
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
            ->withCount('personnel')
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

    public function lockById(int $positionId): ?PersonnelPosition
    {
        return PersonnelPosition::query()
            ->where('position_id', $positionId)
            ->lockForUpdate()
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
        return BarangayPersonnel::query()
            ->where('position_id', $position->getKey())
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(PersonnelPosition $position): array
    {
        $holder = $position->relationLoaded('personnel')
            ? $position->personnel->first()
            : null;

        return [
            'id' => $position->position_id,
            'label' => $position->position_name,
            'in_use' => (int) ($position->personnel_count ?? 0) > 0,
            'occupied' => $holder !== null,
            'occupied_by_personnel_id' => $holder?->personnel_id,
        ];
    }
}
