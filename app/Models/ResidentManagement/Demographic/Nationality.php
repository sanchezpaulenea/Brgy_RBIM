<?php

namespace App\Models\ResidentManagement\Demographic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nationality extends Model
{
    protected $table = 'nationality';

    protected $primaryKey = 'nationality_id';

    public $timestamps = false;

    protected $fillable = [
        'nationality',
    ];

    public function getRouteKeyName(): string
    {
        return 'nationality_id';
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
     * @return HasMany<Resident, $this>
     */
    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class, 'nationality_id', 'nationality_id');
    }
}
