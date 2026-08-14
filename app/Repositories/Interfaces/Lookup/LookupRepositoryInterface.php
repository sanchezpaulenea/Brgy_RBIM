<?php

namespace App\Repositories\Interfaces\Lookup;

use App\Enums\LookupType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface LookupRepositoryInterface
{
    /**
     * @return Collection<int, Model>
     */
    public function getAll(LookupType $type): Collection;

    public function create(LookupType $type, array $attributes): Model;

    public function delete(LookupType $type, int $id): bool;
}