<?php

namespace App\Models\Certificate;

use App\Models\ResidentManagement\Demographic\Ethnicity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeathCertificate extends Model
{
    protected $table = 'death_certificate';

    protected $primaryKey = 'death_certificate_id';

    public $timestamps = false;

    protected $fillable = [
        'certificate_id',
        'ethnicity_id',
        'date_of_death',
        'place_of_death',
        'cause_of_death',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_death' => 'date',
    ];

    /**
     * @return BelongsTo<Certificate, $this>
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id', 'certificate_id');
    }

    /**
     * @return BelongsTo<Ethnicity, $this>
     */
    public function ethnicity(): BelongsTo
    {
        return $this->belongsTo(Ethnicity::class, 'ethnicity_id', 'ethnicity_id');
    }
}
