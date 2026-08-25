<?php

namespace App\Models\HouseholdManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Street extends Model
{
    protected $table = 'street';

    protected $primaryKey = 'street_id';

    public $timestamps = false;

    protected $fillable = [
        'street_name',
    ];

    public function getRouteKeyName(): string
    {
        return 'street_id';
    }

    public static function standardizeName(string $name): string
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);

        if ($normalized === '') {
            return $normalized;
        }

        return mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * @return HasMany<Household, $this>
     */
    public function households(): HasMany
    {
        return $this->hasMany(Household::class, 'street_id', 'street_id');
    }
}
