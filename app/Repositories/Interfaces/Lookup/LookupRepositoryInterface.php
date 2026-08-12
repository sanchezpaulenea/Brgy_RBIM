<?php

namespace App\Repositories\Interfaces\Lookup;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface LookupRepositoryInterface
{
    /**
     * @return list<string>
     */
    public function supportedTypes(): array;

    public function isSupported(string $type): bool;

    public function isWritable(string $type): bool;

    /**
     * @return Collection<int, Model>
     */
    public function all(string $type): Collection;

    public function findById(string $type, int $id): ?Model;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(string $type, array $attributes): Model;

    public function delete(string $type, int $id): bool;

    public function isInUse(string $type, int $id): bool;

    /**
     * @return array<string, mixed>
     */
    public function formatRecord(string $type, Model $record): array;
}
