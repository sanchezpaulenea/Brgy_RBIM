<?php

namespace App\Models\ResidentManagement\Migration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReasonForLeaving extends Model
{
    /**
     * Label stored for non-migrants.
     * migration.reason_for_leaving_id is NOT NULL, so a real lookup row is required.
     */
    public const NOT_APPLICABLE = 'Not Applicable';

    protected $table = 'reason_for_leaving';

    protected $primaryKey = 'reason_for_leaving_id';

    public $timestamps = false;

    protected $fillable = [
        'reason_for_leaving',
    ];

    public function getRouteKeyName(): string
    {
        return 'reason_for_leaving_id';
    }

    public function indicatesNotApplicable(): bool
    {
        $value = Str::of((string) $this->reason_for_leaving)->squish()->lower()->toString();

        return (bool) preg_match('/^(none|n\/a|n\.a\.?|na|not applicable)$/', $value);
    }

    public static function notApplicableId(): ?int
    {
        $match = static::query()
            ->orderBy('reason_for_leaving_id')
            ->get()
            ->first(fn (self $reason) => $reason->indicatesNotApplicable());

        return $match?->reason_for_leaving_id;
    }

    public static function ensureNotApplicableId(): int
    {
        $id = static::notApplicableId();

        if ($id !== null) {
            return $id;
        }

        return (int) static::query()->create([
            'reason_for_leaving' => self::NOT_APPLICABLE,
        ])->reason_for_leaving_id;
    }

    /**
     * @return HasMany<Migration, $this>
     */
    public function migrations(): HasMany
    {
        return $this->hasMany(Migration::class, 'reason_for_leaving_id', 'reason_for_leaving_id');
    }
}
