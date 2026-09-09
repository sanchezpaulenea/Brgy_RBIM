<?php

namespace App\Models\ResidentManagement\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class HighestLvlOfEduc extends Model
{
    protected $table = 'highest_lvl_of_educ';

    protected $primaryKey = 'highest_lvl_of_educ_id';

    public $timestamps = false;

    protected $fillable = [
        'lvl_of_educ',
    ];

    public function getRouteKeyName(): string
    {
        return 'highest_lvl_of_educ_id';
    }

    public function indicatesNotApplicable(): bool
    {
        $value = Str::of((string) $this->lvl_of_educ)->squish()->lower()->toString();

        return (bool) preg_match('/^(no education|none|n\/a|n\.a\.?|na|not applicable)$/', $value);
    }

    public static function notApplicableId(): ?int
    {
        $match = static::query()
            ->orderBy('highest_lvl_of_educ_id')
            ->get()
            ->first(fn (self $level) => $level->indicatesNotApplicable());

        return $match?->highest_lvl_of_educ_id;
    }

    /**
     * @return HasMany<Education, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class, 'highest_lvl_of_educ_id', 'highest_lvl_of_educ_id');
    }
}
