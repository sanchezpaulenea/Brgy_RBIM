<?php

namespace App\Repositories\BarangayPersonnel;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use App\Models\BarangayPersonnel\PersonnelPosition;
use App\Models\BarangayPersonnel\PersonnelStatus;
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

    public function findMatchingIdentity(
        string $lastName,
        string $firstName,
        ?string $middleName,
        ?string $suffix,
        string $dateOfBirth,
        ?int $excludePersonnelId = null,
    ): ?BarangayPersonnel {
        $query = BarangayPersonnel::query()
            ->where('personnel_last_name', $lastName)
            ->where('personnel_first_name', $firstName)
            ->whereDate('personnel_date_of_birth', $dateOfBirth);

        if ($middleName === null || $middleName === '') {
            $query->where(function ($builder): void {
                $builder->whereNull('personnel_middle_name')
                    ->orWhere('personnel_middle_name', '');
            });
        } else {
            $query->where('personnel_middle_name', $middleName);
        }

        if ($suffix === null || $suffix === '') {
            $query->where(function ($builder): void {
                $builder->whereNull('personnel_suffix')
                    ->orWhere('personnel_suffix', '');
            });
        } else {
            $query->where('personnel_suffix', $suffix);
        }

        if ($excludePersonnelId !== null) {
            $query->where('personnel_id', '!=', $excludePersonnelId);
        }

        return $query->first();
    }

    public function findActiveByPositionNames(array $positionNames): Collection
    {
        $normalized = collect($positionNames)
            ->map(fn (string $name): string => mb_strtolower(PersonnelPosition::standardizeName($name)))
            ->filter(fn (string $name): bool => $name !== '')
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return new Collection;
        }

        $placeholders = implode(', ', array_fill(0, $normalized->count(), '?'));

        return BarangayPersonnel::query()
            ->with('position')
            ->where('personnel_status_id', PersonnelStatus::ACTIVE)
            ->whereHas('position', function ($query) use ($normalized, $placeholders): void {
                $query->whereRaw('LOWER(position_name) IN ('.$placeholders.')', $normalized->all());
            })
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
