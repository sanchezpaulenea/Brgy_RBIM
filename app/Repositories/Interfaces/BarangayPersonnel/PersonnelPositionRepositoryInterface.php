<?php

namespace App\Repositories\Interfaces\BarangayPersonnel;

use App\Models\BarangayPersonnel\PersonnelPosition;
use Illuminate\Database\Eloquent\Collection;

interface PersonnelPositionRepositoryInterface
{
    /**
     * @return Collection<int, PersonnelPosition>
     */
    public function all(): Collection;

    public function findById(int $positionId): ?PersonnelPosition;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): PersonnelPosition;

    public function delete(PersonnelPosition $position): bool;

    public function isInUse(PersonnelPosition $position): bool;

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(PersonnelPosition $position): array;
}
