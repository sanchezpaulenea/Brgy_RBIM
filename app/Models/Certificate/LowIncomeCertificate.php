<?php

namespace App\Models\Certificate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LowIncomeCertificate extends Model
{
    protected $table = 'low_income_certificate';

    protected $primaryKey = 'low_income_certificate_id';

    public $timestamps = false;

    protected $fillable = [
        'certificate_id',
        'include_income_amount',
        'stated_income_amount',
        'resident_since',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'include_income_amount' => 'boolean',
        'stated_income_amount' => 'decimal:2',
        'resident_since' => 'date',
    ];

    /**
     * @return BelongsTo<Certificate, $this>
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id', 'certificate_id');
    }
}
