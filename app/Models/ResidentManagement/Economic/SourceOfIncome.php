<?php

namespace App\Models\ResidentManagement\Economic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceOfIncome extends Model
{
    public const EMPLOYMENT = 1;

    public const BUSINESS = 2;

    public const REMITTANCE = 3;

    public const INVESTMENTS = 4;

    public const OTHERS = 5;

    /**
     * Q17–Q18 are skipped for these sources.
     *
     * @var list<int>
     */
    public const SKIP_WORK_DETAIL_IDS = [
        self::REMITTANCE,
        self::INVESTMENTS,
        self::OTHERS,
    ];

    protected $table = 'source_of_income';

    protected $primaryKey = 'source_of_income_id';

    public $timestamps = false;

    protected $fillable = [
        'source_of_income',
    ];

    public function getRouteKeyName(): string
    {
        return 'source_of_income_id';
    }

    public static function skipsWorkDetails(mixed $sourceOfIncomeId): bool
    {
        return in_array((int) $sourceOfIncomeId, self::SKIP_WORK_DETAIL_IDS, true);
    }

    /**
     * @return HasMany<Economic, $this>
     */
    public function economics(): HasMany
    {
        return $this->hasMany(Economic::class, 'source_of_income_id', 'source_of_income_id');
    }
}
