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
     * @return Collection<int, Resident>
     */
    public function all(): Collection
    {
        return Resident::query()
            ->with($this->defaultRelations())
            ->orderByDesc('resident_id')
            ->get();
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
}
