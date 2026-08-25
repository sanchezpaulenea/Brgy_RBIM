<?php

namespace App\Repositories\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Ethnicity;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Repositories\Interfaces\ResidentManagement\Demographic\EthnicityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EthnicityRepository implements EthnicityRepositoryInterface
{
    /**
     * @return Collection<int, Ethnicity>
     */
    public function all(): Collection
    {
        return Ethnicity::query()
            ->withCount('residents')
            ->orderBy('ethnicity')
            ->get();
    }

    public function findById(int $ethnicityId): ?Ethnicity
    {
        return Ethnicity::query()
            ->where('ethnicity_id', $ethnicityId)
            ->first();
    }

    public function lockById(int $ethnicityId): ?Ethnicity
    {
        return Ethnicity::query()
            ->where('ethnicity_id', $ethnicityId)
            ->lockForUpdate()
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Ethnicity
    {
        return Ethnicity::create($attributes);
    }

    public function delete(Ethnicity $ethnicity): bool
    {
        return (bool) $ethnicity->delete();
    }

    public function isInUse(Ethnicity $ethnicity): bool
    {
        return Resident::query()
            ->where('ethnicity_id', $ethnicity->getKey())
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Ethnicity $ethnicity): array
    {
        return [
            'id' => $ethnicity->ethnicity_id,
            'label' => $ethnicity->ethnicity,
            'ethnicity_id' => $ethnicity->ethnicity_id,
            'ethnicity' => $ethnicity->ethnicity,
            'in_use' => (int) ($ethnicity->residents_count ?? 0) > 0,
        ];
    }
}
