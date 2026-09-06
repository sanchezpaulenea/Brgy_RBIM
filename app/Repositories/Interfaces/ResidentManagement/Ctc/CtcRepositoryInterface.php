<?php

namespace App\Repositories\Interfaces\ResidentManagement\Ctc;

use App\Models\ResidentManagement\Ctc\Ctc;

interface CtcRepositoryInterface
{
    public function findById(int $ctcId): ?Ctc;

    public function findByResidentId(int $residentId): ?Ctc;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Ctc;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Ctc $ctc, array $attributes): Ctc;
}
