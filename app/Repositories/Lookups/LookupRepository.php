<?php

namespace App\Repositories\Lookups;

use App\Enums\LookupType;
use App\Models\Geographic\Barangay;
use App\Models\Geographic\MunicipalityCity;
use App\Models\Geographic\Province;
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

    /**
     * @return Collection<int, Province>
     */
    public function provinces(): Collection
    {
        return Province::query()
            ->with('country')
            ->orderBy('province_name')
            ->orderBy('province_id')
            ->get();
    }

    /**
     * @return Collection<int, MunicipalityCity>
     */
    public function municipalitiesForProvince(int $provinceId): Collection
    {
        return MunicipalityCity::query()
            ->with('province.country')
            ->where('province_id', $provinceId)
            ->orderBy('municipality_city_name')
            ->orderBy('municipality_city_id')
            ->get();
    }

    /**
     * @return Collection<int, Barangay>
     */
    public function searchBarangays(string $term, ?int $municipalityId = null, int $limit = 20): Collection
    {
        $query = Barangay::query()->with(['municipalityCity.province']);

        if ($municipalityId !== null) {
            $query->where('municipality_city_id', $municipalityId);
        }

        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where('barangay_name', 'like', $like);
        }

        return $query
            ->orderBy('barangay_name')
            ->orderBy('barangay_id')
            ->limit($limit)
            ->get();
    }
}
