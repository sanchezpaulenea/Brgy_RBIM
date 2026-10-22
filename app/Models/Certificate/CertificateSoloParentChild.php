<?php

namespace App\Models\Certificate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateSoloParentChild extends Model
{
    protected $table = 'certificate_solo_parent_child';

    protected $primaryKey = 'certificate_solo_parent_child_id';

    public $timestamps = false;

    protected $fillable = [
        'certificate_id',
        'child_name',
        'date_of_birth',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * @return BelongsTo<Certificate, $this>
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id', 'certificate_id');
    }
}
