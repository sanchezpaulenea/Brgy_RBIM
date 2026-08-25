<?php

namespace App\Repositories\Interfaces\Lookups;

use App\Enums\LookupType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface LookupRepositoryInterface
{
    /**
     * @return Collection<int, Model>
     */
    public function all(LookupType $type): Collection;
}
