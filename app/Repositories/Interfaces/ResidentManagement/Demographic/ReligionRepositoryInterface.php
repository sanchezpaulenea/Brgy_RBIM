<?php

namespace App\Repositories\Interfaces\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Religion;
use Illuminate\Database\Eloquent\Collection;

interface ReligionRepositoryInterface
{
    /**
     * @return Collection<int, Religion>
     */
    public function all(): Collection;

    public function findById(int $religionId): ?Religion;

    public function lockById(int $religionId): ?Religion;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Religion;

    public function delete(Religion $religion): bool;

    public function isInUse(Religion $religion): bool;

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Religion $religion): array;
}
