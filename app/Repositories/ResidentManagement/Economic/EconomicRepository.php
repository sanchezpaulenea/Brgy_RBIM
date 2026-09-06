<?php

namespace App\Repositories\ResidentManagement\Economic;

use App\Models\ResidentManagement\Economic\Economic;
use App\Repositories\Interfaces\ResidentManagement\Economic\EconomicRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EconomicRepository implements EconomicRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'sourceOfIncome',
            'statusOfWorkBusiness',
        ];
    }

    /**
     * @return Collection<int, Economic>
     */
    public function listByResident(int $residentId): Collection
    {
        return Economic::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->orderByDesc('economic_id')
            ->get();
    }

    public function findById(int $economicId): ?Economic
    {
        return Economic::query()
            ->with($this->defaultRelations())
            ->where('economic_id', $economicId)
            ->first();
    }

    public function findByResidentId(int $residentId): ?Economic
    {
        return Economic::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Economic
    {
        $economic = Economic::create($attributes);

        return $economic->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Economic $economic, array $attributes): Economic
    {
        $economic->fill($attributes);
        $economic->save();

        return $economic->fresh($this->defaultRelations()) ?? $economic;
    }
}
