<?php

namespace App\Repositories\Interfaces\BarangayPersonnel;

use App\Models\BarangayPersonnel\PersonnelStatus;
use Illuminate\Database\Eloquent\Collection;

interface PersonnelStatusInterface
{
    /**
     * @return Collection<int, PersonnelStatus>
     */
    public function all(): Collection;
}
