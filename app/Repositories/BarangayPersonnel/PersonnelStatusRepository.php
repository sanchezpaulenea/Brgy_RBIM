<?php

namespace App\Repositories\BarangayPersonnel;

use App\Models\BarangayPersonnel\PersonnelStatus;
use App\Repositories\Interfaces\BarangayPersonnel\PersonnelStatusInterface;
use Illuminate\Database\Eloquent\Collection;

class PersonnelStatusRepository implements PersonnelStatusInterface
{
    /**
     * @return Collection<int, PersonnelStatus>
     */
    public function all(): Collection
    {
        return PersonnelStatus::query()->orderBy('personnel_status')->get();
    }
}
