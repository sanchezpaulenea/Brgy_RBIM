<?php

namespace App\Models\ResidentManagement\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SchoolLvl extends Model
{
    /**
     * Label stored for residents who are not currently enrolled.
     * education.school_lvl_id is NOT NULL, so a real lookup row is required.
     */
    public const NOT_APPLICABLE = 'Not Applicable';

    protected $table = 'school_lvl';

    protected $primaryKey = 'school_lvl_id';

    public $timestamps = false;

    protected $fillable = [
        'school_lvl',
    ];

    public function getRouteKeyName(): string
    {
        return 'school_lvl_id';
    }

    public function indicatesNotApplicable(): bool
    {
        $value = Str::of((string) $this->school_lvl)->squish()->lower()->toString();

        return (bool) preg_match('/^(none|n\/a|n\.a\.?|na|not applicable)$/', $value);
    }

    public static function notApplicableId(): ?int
    {
        $match = static::query()
            ->orderBy('school_lvl_id')
            ->get()
            ->first(fn (self $level) => $level->indicatesNotApplicable());

        return $match?->school_lvl_id;
    }

    /**
     * @return HasMany<Education, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'school_lvl_id', 'school_lvl_id');
    }
}
