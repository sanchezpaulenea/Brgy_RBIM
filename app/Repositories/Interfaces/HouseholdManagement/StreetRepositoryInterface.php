<?php

namespace App\Repositories\Interfaces\HouseholdManagement;

use App\Models\HouseholdManagement\Street;
use Illuminate\Database\Eloquent\Collection;

interface StreetRepositoryInterface
{
    /**
     * @return Collection<int, Street>
     */
    public function all(): Collection;

    public function findById(int $streetId): ?Street;

    public function lockById(int $streetId): ?Street;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Street;

    public function delete(Street $street): bool;

    public function isInUse(Street $street): bool;

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Street $street): array;
}
