<?php

namespace App\Repositories\ResidentManagement\Ctc;

use App\Models\ResidentManagement\Ctc\Ctc;
use App\Repositories\Interfaces\ResidentManagement\Ctc\CtcRepositoryInterface;

class CtcRepository implements CtcRepositoryInterface
{
    public function findById(int $ctcId): ?Ctc
    {
        return Ctc::query()
            ->where('community_tax_cert', $ctcId)
            ->first();
    }

    public function findByResidentId(int $residentId): ?Ctc
    {
        return Ctc::query()
            ->where('resident_id', $residentId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): Ctc
    {
        return Ctc::create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Ctc $ctc, array $attributes): Ctc
    {
        $ctc->fill($attributes);
        $ctc->save();

        return $ctc->fresh() ?? $ctc;
    }
}
