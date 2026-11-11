<?php

namespace App\Models\Geographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class Barangay extends Model
{
    protected $table = 'barangay';

    protected $primaryKey = 'barangay_id';

    public $timestamps = false;

    protected $fillable = [
        'municipality_city_id',
        'barangay_name',
    ];

    /**
     * @return BelongsTo<MunicipalityCity, $this>
     */
    public function municipalityCity(): BelongsTo
    {
        return $this->belongsTo(MunicipalityCity::class, 'municipality_city_id', 'municipality_city_id');
    }

    public function locationLabel(): string
    {
        $this->loadMissing('municipalityCity.province');

        $parts = array_filter([
            $this->barangay_name,
            $this->municipalityCity?->municipality_city_name,
            $this->municipalityCity?->province?->province_name,
        ], fn (mixed $part): bool => is_string($part) && trim($part) !== '');

        return implode(', ', $parts);
    }

    public static function idForLocation(string $barangay, string $municipality, string $province): ?int
    {
        $barangayName = mb_strtolower(trim($barangay));

        if ($barangayName === '') {
            return null;
        }

        /** @var Collection<int, self> $candidates */
        $candidates = static::query()
            ->with('municipalityCity.province')
            ->whereRaw('LOWER(barangay_name) = ?', [$barangayName])
            ->get();

        $cityKey = static::placeKey($municipality);
        $provinceKey = static::placeKey($province);

        $match = $candidates->first(function (self $row) use ($cityKey, $provinceKey): bool {
            $city = static::placeKey((string) $row->municipalityCity?->municipality_city_name);
            $provinceName = static::placeKey((string) $row->municipalityCity?->province?->province_name);

            return $city === $cityKey && $provinceName === $provinceKey;
        });

        return $match === null ? null : (int) $match->barangay_id;
    }

    private static function placeKey(string $value): string
    {
        $normalized = mb_strtolower(trim($value));
        $withoutPrefix = preg_replace('/^(city of|municipality of)\s+/u', '', $normalized);
        $normalized = is_string($withoutPrefix) ? $withoutPrefix : $normalized;
        $withoutSuffix = preg_replace('/\s+(city|municipality)$/u', '', $normalized);

        return is_string($withoutSuffix) ? $withoutSuffix : $normalized;
    }
}
