<?php

namespace App\Repositories\ResidentManagement\Sociocivic;

use App\Models\ResidentManagement\Sociocivic\Sociocivic;
use App\Repositories\Interfaces\ResidentManagement\Sociocivic\SociocivicRepositoryInterface;

class SociocivicRepository implements SociocivicRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return ['soloParentStatus'];
    }

    public function findById(int $sociocivicId): ?Sociocivic
    {
        return Sociocivic::query()
            ->with($this->defaultRelations())
            ->where('sociocivic_id', $sociocivicId)
            ->first();
    }

    public function findByResidentId(int $residentId): ?Sociocivic
    {
        return Sociocivic::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Sociocivic
    {
        $sociocivic = Sociocivic::create($attributes);

        return $sociocivic->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Sociocivic $sociocivic, array $attributes): Sociocivic
    {
        $sociocivic->fill($attributes);
        $sociocivic->save();

        return $sociocivic->fresh($this->defaultRelations()) ?? $sociocivic;
    }
}
