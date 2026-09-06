<?php

namespace App\Models\ResidentManagement\Ctc;

use App\Models\ResidentManagement\Demographic\Resident;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ctc extends Model
{
    protected $table = 'community_tax_cert';

    protected $primaryKey = 'community_tax_cert';

    public $timestamps = false;

    protected $fillable = [
        'has_valid_ctc',
        'ctc_issued_here',
        'resident_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'has_valid_ctc' => 'boolean',
        'ctc_issued_here' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'community_tax_cert';
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }
}
