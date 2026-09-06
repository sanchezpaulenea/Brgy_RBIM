<?php

namespace App\Repositories\Interfaces\ResidentManagement\Migration;

use App\Models\ResidentManagement\Migration\Migration;

interface MigrationRepositoryInterface
{
    public function findById(int $migrationId): ?Migration;

    public function findByResidentId(int $residentId): ?Migration;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Migration;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Migration $migration, array $attributes): Migration;
}
