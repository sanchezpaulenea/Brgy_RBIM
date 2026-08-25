<?php

namespace App\Repositories\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Religion;
use App\Models\ResidentManagement\Demographic\Resident;
use App\Repositories\Interfaces\ResidentManagement\Demographic\ReligionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ReligionRepository implements ReligionRepositoryInterface
{
    /**
     * @return Collection<int, Religion>
     */
    public function all(): Collection
    {
        return Religion::query()
            ->withCount('residents')
            ->orderBy('religion')
            ->get();
    }

    public function findById(int $religionId): ?Religion
    {
        return Religion::query()
            ->where('religion_id', $religionId)
            ->first();
    }

    public function lockById(int $religionId): ?Religion
    {
        return Religion::query()
            ->where('religion_id', $religionId)
            ->lockForUpdate()
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Religion
    {
        return Religion::create($attributes);
    }

    public function delete(Religion $religion): bool
    {
        return (bool) $religion->delete();
    }

    public function isInUse(Religion $religion): bool
    {
        return Resident::query()
            ->where('religion_id', $religion->getKey())
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Religion $religion): array
    {
        return [
            'id' => $religion->religion_id,
            'label' => $religion->religion,
            'religion_id' => $religion->religion_id,
            'religion' => $religion->religion,
            'in_use' => (int) ($religion->residents_count ?? 0) > 0,
        ];
    }
}
