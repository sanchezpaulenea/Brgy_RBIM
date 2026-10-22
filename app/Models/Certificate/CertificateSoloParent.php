<?php

namespace App\Models\Certificate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateSoloParent extends Model
{
    protected $table = 'certificate_solo_parent';

    protected $primaryKey = 'certificate_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'certificate_id',
        'circumstance',
        'president_name',
        'president_address',
        'neighbor_name',
        'neighbor_address',
        'kagawad_name',
    ];

    /**
     * @return BelongsTo<Certificate, $this>
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id', 'certificate_id');
    }
}
