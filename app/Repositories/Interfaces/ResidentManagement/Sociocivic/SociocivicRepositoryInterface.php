<?php

namespace App\Repositories\Interfaces\ResidentManagement\Sociocivic;

use App\Models\ResidentManagement\Sociocivic\Sociocivic;

interface SociocivicRepositoryInterface
{
    public function findById(int $sociocivicId): ?Sociocivic;

    public function findByResidentId(int $residentId): ?Sociocivic;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Sociocivic;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Sociocivic $sociocivic, array $attributes): Sociocivic;
}
