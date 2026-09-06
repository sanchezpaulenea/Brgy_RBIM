<?php

namespace App\Repositories\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\WomenHealth;
use App\Repositories\Interfaces\ResidentManagement\Health\WomenHealthRepositoryInterface;

class WomenHealthRepository implements WomenHealthRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'health',
            'familyPlanningMethod',
            'sourceOfFpMethod',
        ];
    }

    public function findById(int $womenHealthId): ?WomenHealth
    {
        return WomenHealth::query()
            ->with($this->defaultRelations())
            ->where('women_health_id', $womenHealthId)
            ->first();
    }

    public function findByHealthId(int $healthId): ?WomenHealth
    {
        return WomenHealth::query()
            ->with($this->defaultRelations())
            ->where('health_id', $healthId)
            ->first();
    }

    public function findByResidentId(int $residentId): ?WomenHealth
    {
        return WomenHealth::query()
            ->with($this->defaultRelations())
            ->whereHas('health', fn ($query) => $query->where('resident_id', $residentId))
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): WomenHealth
    {
        $womenHealth = WomenHealth::create($attributes);

        return $womenHealth->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(WomenHealth $womenHealth, array $attributes): WomenHealth
    {
        $womenHealth->fill($attributes);
        $womenHealth->save();

        return $womenHealth->fresh($this->defaultRelations()) ?? $womenHealth;
    }
}
