<?php

namespace App\Repositories\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\InfantHealth;
use App\Repositories\Interfaces\ResidentManagement\Health\InfantHealthRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class InfantHealthRepository implements InfantHealthRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'placeOfDelivery',
            'birthAttendant',
        ];
    }

    /**
     * @return Collection<int, InfantHealth>
     */
    public function listByResident(int $residentId): Collection
    {
        return InfantHealth::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->orderByDesc('infant_health_id')
            ->get();
    }

    public function findById(int $infantHealthId): ?InfantHealth
    {
        return InfantHealth::query()
            ->with($this->defaultRelations())
            ->where('infant_health_id', $infantHealthId)
            ->first();
    }

    public function findByResidentId(int $residentId): ?InfantHealth
    {
        return InfantHealth::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): InfantHealth
    {
        $infantHealth = InfantHealth::create($attributes);

        return $infantHealth->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(InfantHealth $infantHealth, array $attributes): InfantHealth
    {
        $infantHealth->fill($attributes);
        $infantHealth->save();

        return $infantHealth->fresh($this->defaultRelations()) ?? $infantHealth;
    }
}
