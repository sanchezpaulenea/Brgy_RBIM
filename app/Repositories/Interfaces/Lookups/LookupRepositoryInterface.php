<?php

namespace App\Repositories\Interfaces\Lookups;

use App\Enums\LookupType;
use App\Models\Geographic\Barangay;
use App\Models\Geographic\MunicipalityCity;
use App\Models\Geographic\Province;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface LookupRepositoryInterface
{
    /**
     * @return Collection<int, Model>
     */
    public function all(LookupType $type): Collection;

    /**
     * @return Collection<int, Province>
     */
    public function provinces(): Collection;

    /**
     * @return Collection<int, MunicipalityCity>
     */
    public function municipalitiesForProvince(int $provinceId): Collection;

    /**
     * @return Collection<int, Barangay>
     */
    public function searchBarangays(string $term, ?int $municipalityId = null, int $limit = 20): Collection;
}
