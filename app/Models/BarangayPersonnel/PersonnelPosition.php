<?php

namespace App\Models\BarangayPersonnel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelPosition extends Model
{
    protected $table = 'personnel_position';

    protected $primaryKey = 'position_id';

    public $timestamps = false;

    protected $fillable = [
        'position_name',
    ];

    public function getRouteKeyName(): string
    {
        return 'position_id';
    }

    /**
     * Title-case a position name, keeping known abbreviations uppercase.
     */
    public static function standardizeName(string $name): string
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);

        if ($normalized === '') {
            return $normalized;
        }

        $abbreviations = [
            'sk' => 'SK',
        ];

        $words = explode(' ', $normalized);

        foreach ($words as $index => $word) {
            $lower = mb_strtolower($word, 'UTF-8');
            $words[$index] = $abbreviations[$lower] ?? mb_convert_case($word, MB_CASE_TITLE, 'UTF-8');
        }

        return implode(' ', $words);
    }

    /**
     * @return HasMany<BarangayPersonnel, $this>
     */
    public function personnel(): HasMany
    {
        return $this->hasMany(BarangayPersonnel::class, 'position_id', 'position_id');
    }
}
