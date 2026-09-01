<?php

namespace App\Repositories\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Resident;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ResidentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ResidentRepository implements ResidentRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'household',
            'clan',
            'sex',
            'nationality',
            'religion',
            'ethnicity',
            'maritalStatus',
            'residentType',
            'status',
            'relationshipToHouseholdHead',
        ];
    }

    /**
     * @param  array{household_id?: int, resident_type_id?: int, resident_status_id?: int}  $filters
     * @return Collection<int, Resident>
     */
    public function list(array $filters = []): Collection
    {
        return Resident::query()
            ->with($this->defaultRelations())
            ->when(
                ! empty($filters['household_id']),
                fn ($query) => $query->where('household_id', $filters['household_id']),
            )
            ->when(
                ! empty($filters['resident_type_id']),
                fn ($query) => $query->where('resident_type_id', $filters['resident_type_id']),
            )
            ->when(
                ! empty($filters['resident_status_id']),
                fn ($query) => $query->where('resident_status_id', $filters['resident_status_id']),
            )
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    /**
     * @return Collection<int, Resident>
     */
    public function all(): Collection
    {
        return $this->list();
    }

    public function findById(int $residentId): ?Resident
    {
        return Resident::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Resident
    {
        $resident = Resident::create($attributes);

        return $resident->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Resident $resident, array $attributes): Resident
    {
        $resident->fill($attributes);
        $resident->save();

        return $resident->fresh($this->defaultRelations()) ?? $resident;
    }
}
