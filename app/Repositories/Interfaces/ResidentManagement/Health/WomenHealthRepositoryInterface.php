<?php

namespace App\Repositories\Interfaces\ResidentManagement\Health;

use App\Models\ResidentManagement\Health\WomenHealth;

interface WomenHealthRepositoryInterface
{
    public function findById(int $womenHealthId): ?WomenHealth;

    public function findByHealthId(int $healthId): ?WomenHealth;

    public function findByResidentId(int $residentId): ?WomenHealth;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): WomenHealth;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(WomenHealth $womenHealth, array $attributes): WomenHealth;
}
