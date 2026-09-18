<?php

namespace App\Models\ResidentManagement\Migration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ReasonForTransfer extends Model
{
    /**
     * Label stored for non-migrants.
     * migration.reason_for_transfer_id is NOT NULL, so a real lookup row is required.
     */
    public const NOT_APPLICABLE = 'Not Applicable';

    protected $table = 'reason_for_transfer';

    protected $primaryKey = 'reason_for_transfer_id';

    public $timestamps = false;

    protected $fillable = [
        'reason_for_transfer',
    ];

    public function getRouteKeyName(): string
    {
        return 'reason_for_transfer_id';
    }

    public function indicatesNotApplicable(): bool
    {
        $value = Str::of((string) $this->reason_for_transfer)->squish()->lower()->toString();

        return (bool) preg_match('/^(none|n\/a|n\.a\.?|na|not applicable)$/', $value);
    }

    public static function notApplicableId(): ?int
    {
        $match = static::query()
            ->orderBy('reason_for_transfer_id')
            ->get()
            ->first(fn (self $reason) => $reason->indicatesNotApplicable());

        return $match?->reason_for_transfer_id;
    }

    public static function ensureNotApplicableId(): int
    {
        $id = static::notApplicableId();

        if ($id !== null) {
            return $id;
        }

        return (int) static::query()->create([
            'reason_for_transfer' => self::NOT_APPLICABLE,
        ])->reason_for_transfer_id;
    }

    /**
     * @return HasMany<Migration, $this>
     */
    public function migrations(): HasMany
    {
        return $this->hasMany(Migration::class, 'reason_for_transfer_id', 'reason_for_transfer_id');
    }
}
