<?php

namespace App\Repositories\Logs;

use App\Models\Logs\Action;
use App\Repositories\Interfaces\Logs\ActionInterface;
use Illuminate\Database\Eloquent\Collection;

class ActionRepository implements ActionInterface
{
    /**
     * @return Collection<int, Action>
     */
    public function all(): Collection
    {
        return Action::query()->orderBy('action_id')->get();
    }
}
