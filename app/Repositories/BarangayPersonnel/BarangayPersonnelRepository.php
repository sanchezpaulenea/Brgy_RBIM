<?php

namespace App\Repositories\BarangayPersonnel;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Repositories\Interfaces\BarangayPersonnel\BarangayPersonnelRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BarangayPersonnelRepository implements BarangayPersonnelRepositoryInterface
{
    /**
     * @return Collection<int, BarangayPersonnel>
     */
    public function all(): Collection
    {
        return BarangayPersonnel::query()
            ->with(['position', 'status', 'user'])
            ->orderBy('personnel_last_name')
            ->orderBy('personnel_first_name')
            ->get();
    }

    public function findById(int $personnelId): ?BarangayPersonnel
    {
        return BarangayPersonnel::query()
            ->with(['position', 'status', 'user'])
            ->where('personnel_id', $personnelId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): BarangayPersonnel
    {
        $personnel = BarangayPersonnel::create($attributes);

        return $personnel->load(['position', 'status', 'user']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(BarangayPersonnel $personnel, array $attributes): BarangayPersonnel
    {
        $personnel->fill($attributes);
        $personnel->save();

        return $personnel->fresh(['position', 'status', 'user']) ?? $personnel;
    }
}
