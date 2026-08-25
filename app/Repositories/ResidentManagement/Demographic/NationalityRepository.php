<?php

namespace App\Repositories\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Nationality;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Repositories\Interfaces\ResidentManagement\Demographic\NationalityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class NationalityRepository implements NationalityRepositoryInterface
{
    /**
     * @return Collection<int, Nationality>
     */
    public function all(): Collection
    {
        return Nationality::query()
            ->withCount('residents')
            ->orderBy('nationality')
            ->get();
    }

    public function findById(int $nationalityId): ?Nationality
    {
        return Nationality::query()
            ->where('nationality_id', $nationalityId)
            ->first();
    }

    public function lockById(int $nationalityId): ?Nationality
    {
        return Nationality::query()
            ->where('nationality_id', $nationalityId)
            ->lockForUpdate()
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Nationality
    {
        return Nationality::create($attributes);
    }

    public function delete(Nationality $nationality): bool
    {
        return (bool) $nationality->delete();
    }

    public function isInUse(Nationality $nationality): bool
    {
        return Resident::query()
            ->where('nationality_id', $nationality->getKey())
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Nationality $nationality): array
    {
        return [
            'id' => $nationality->nationality_id,
            'label' => $nationality->nationality,
            'nationality_id' => $nationality->nationality_id,
            'nationality' => $nationality->nationality,
            'in_use' => (int) ($nationality->residents_count ?? 0) > 0,
        ];
    }
}
