<?php

namespace App\Models\ResidentManagement\Health;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WomenHealth extends Model
{
    /**
     * Sentinel stored when Q23–Q25 lookups are skipped or left blank.
     * Those FKs were dropped so 0 can be persisted without a lookup row.
     */
    public const LOOKUP_NOT_APPLICABLE = 0;

    protected $table = 'women_health';

    protected $primaryKey = 'women_health_id';

    public $timestamps = false;

    protected $fillable = [
        'number_pregnancies',
        'living_children',
        'family_planning_method_id',
        'source_of_fp_method_id',
        'have_intention_to_use_fp',
        'health_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'have_intention_to_use_fp' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'women_health_id';
    }

    /**
     * @return BelongsTo<Health, $this>
     */
    public function health(): BelongsTo
    {
        return $this->belongsTo(Health::class, 'health_id', 'health_id');
    }

    /**
     * @return BelongsTo<FamilyPlanningMethod, $this>
     */
    public function familyPlanningMethod(): BelongsTo
    {
        return $this->belongsTo(
            FamilyPlanningMethod::class,
            'family_planning_method_id',
            'family_planning_method_id'
        );
    }

    /**
     * @return BelongsTo<SourceOfFPMethod, $this>
     */
    public function sourceOfFpMethod(): BelongsTo
    {
        return $this->belongsTo(SourceOfFPMethod::class, 'source_of_fp_method_id', 'source_of_fp_method_id');
    }
}
