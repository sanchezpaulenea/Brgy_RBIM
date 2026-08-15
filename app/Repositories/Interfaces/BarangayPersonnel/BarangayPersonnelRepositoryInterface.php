<?php

namespace App\Repositories\Interfaces\BarangayPersonnel;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use Illuminate\Database\Eloquent\Collection;

interface BarangayPersonnelRepositoryInterface
{
    /**
     * @return Collection<int, BarangayPersonnel>
     */
    public function all(): Collection;

    public function findById(int $personnelId): ?BarangayPersonnel;

    public function findMatchingIdentity(
        string $lastName,
        string $firstName,
        ?string $middleName,
        ?string $suffix,
        string $dateOfBirth,
        ?int $excludePersonnelId = null,
    ): ?BarangayPersonnel;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): BarangayPersonnel;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(BarangayPersonnel $personnel, array $attributes): BarangayPersonnel;
}
