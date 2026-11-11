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
        'certificate_solo_parent_id',
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
     * @return BelongsTo<CertificateSoloParent, $this>
     */
    public function soloParent(): BelongsTo
    {
        return $this->belongsTo(CertificateSoloParent::class, 'certificate_solo_parent_id', 'certificate_solo_parent_id');
    }
}
