<?php

namespace App\Repositories\Interfaces\ResidentManagement\Demographic;

use App\Models\ResidentManagement\Demographic\Nationality;
use Illuminate\Database\Eloquent\Collection;

interface NationalityRepositoryInterface
{
    /**
     * @return Collection<int, Nationality>
     */
    public function all(): Collection;

    public function findById(int $nationalityId): ?Nationality;

    public function lockById(int $nationalityId): ?Nationality;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Nationality;

    public function delete(Nationality $nationality): bool;

    public function isInUse(Nationality $nationality): bool;

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(Nationality $nationality): array;
}
