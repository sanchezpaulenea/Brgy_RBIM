<?php

namespace App\Models\ResidentManagement\Health;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Disability extends Model
{
    protected $table = 'disability';

    protected $primaryKey = 'disability_id';

    public $timestamps = false;

    protected $fillable = [
        'disability',
    ];

    public function getRouteKeyName(): string
    {
        return 'disability_id';
    }

    public static function findOrCreateByLabel(string $label): self
    {
        $normalized = Str::of($label)->squish()->title()->toString();

        $existing = static::query()
            ->whereRaw('LOWER(disability) = ?', [Str::lower($normalized)])
            ->first();

        return $existing ?? static::query()->create(['disability' => $normalized]);
    }

    /**
     * @return HasMany<Health, $this>
     */
    public function healthRecords(): HasMany
    {
        return $this->hasMany(Health::class, 'disability_id', 'disability_id');
    }
}
