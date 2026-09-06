<?php

namespace App\Repositories\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\Health;
use App\Repositories\Interfaces\ResidentManagement\Health\HealthRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class HealthRepository implements HealthRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'healthInsurance',
            'facilityVisitedPast12Mos',
            'facilityVisitReason',
            'womenHealth.familyPlanningMethod',
            'womenHealth.sourceOfFpMethod',
        ];
    }

    /**
     * @return Collection<int, Health>
     */
    public function listByResident(int $residentId): Collection
    {
        return Health::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->orderByDesc('health_id')
            ->get();
    }

    public function findById(int $healthId): ?Health
    {
        return Health::query()
            ->with($this->defaultRelations())
            ->where('health_id', $healthId)
            ->first();
    }

    public function findByResidentId(int $residentId): ?Health
    {
        return Health::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Health
    {
        $health = Health::create($attributes);

        return $health->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Health $health, array $attributes): Health
    {
        $health->fill($attributes);
        $health->save();

        return $health->fresh($this->defaultRelations()) ?? $health;
    }
}
