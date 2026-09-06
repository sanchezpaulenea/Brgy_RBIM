<?php

namespace App\Repositories\ResidentManagement\Migration;

use App\Models\ResidentManagement\Migration\Migration;
use App\Repositories\Interfaces\ResidentManagement\Migration\MigrationRepositoryInterface;

class MigrationRepository implements MigrationRepositoryInterface
{
    /**
     * @return list<string>
     */
    private function defaultRelations(): array
    {
        return [
            'reasonForLeaving',
            'reasonForTransfer',
        ];
    }

    public function findById(int $migrationId): ?Migration
    {
        return Migration::query()
            ->with($this->defaultRelations())
            ->where('migration_id', $migrationId)
            ->first();
    }

    public function findByResidentId(int $residentId): ?Migration
    {
        return Migration::query()
            ->with($this->defaultRelations())
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Migration
    {
        $migration = Migration::create($attributes);

        return $migration->load($this->defaultRelations());
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Migration $migration, array $attributes): Migration
    {
        $migration->fill($attributes);
        $migration->save();

        return $migration->fresh($this->defaultRelations()) ?? $migration;
    }
}
