<?php

namespace App\Repositories\Interfaces\Logs;

use App\Models\Logs\Action;
use Illuminate\Database\Eloquent\Collection;

interface ActionInterface
{
    /**
     * @return Collection<int, Action>
     */
    public function all(): Collection;
}
