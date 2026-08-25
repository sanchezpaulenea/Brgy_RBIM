<?php

namespace App\Repositories\Interfaces\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Ethnicity;
use Illuminate\Database\Eloquent\Collection;

interface EthnicityRepositoryInterface
{
    /**
     * @return Collection<int, Ethnicity>
     */
    public function all(): Collection;

    public function findById(int $ethnicityId): ?Ethnicity;

    public function lockById(int $ethnicityId): ?Ethnicity;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Ethnicity;

    public function delete(Ethnicity $ethnicity): bool;

    public function isInUse(Ethnicity $ethnicity): bool;

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Ethnicity $ethnicity): array;
}
