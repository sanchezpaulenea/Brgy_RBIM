<?php

namespace App\Repositories\Lookups;

use App\Enums\LookupType;
use App\Repositories\Interfaces\Lookups\LookupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class LookupRepository implements LookupRepositoryInterface
{
    /**
     * @return Collection<int, Model>
     */
    public function all(LookupType $type): Collection
    {
        $modelClass = $type->modelClass();

        return $modelClass::query()
            ->orderBy($type->orderColumn())
            ->get();
    }
}
