<?php

namespace App\Repositories\Lookup;

use App\Enums\LookupType;
use App\Repositories\Interfaces\Lookup\LookupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LookupRepository implements LookupRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAll(LookupType $type): Collection
    {
        $modelClass = $type->modelClass();

        return $modelClass::query()
            ->orderBy((new $modelClass)->getKeyName())
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function create(LookupType $type, array $attributes): Model
    {
        $modelClass = $type->modelClass();

        return $modelClass::query()->create($attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(LookupType $type, int $id): bool
    {
        $modelClass = $type->modelClass();
        $record = $modelClass::query()->findOrFail($id);

        return (bool) $record->delete();
    }
}