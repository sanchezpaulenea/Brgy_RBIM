<?php

namespace App\Models\Certificate;

use App\Models\BarangayPersonnel\BarangayPersonnel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateSoloParent extends Model
{
    protected $table = 'certificate_solo_parent';

    protected $primaryKey = 'certificate_solo_parent_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'certificate_solo_parent_id',
        'certificate_id',
        'social_service_kagawad_id',
        'president_name_id',
        'president_address',
        'neighbor_name',
        'neighbor_address',
        'circumstance',
    ];

    /**
     * @return BelongsTo<Certificate, $this>
     */
    public function certificate(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'certificate_id', 'certificate_id');
    }

    /**
     * @return BelongsTo<BarangayPersonnel, $this>
     */
    public function president(): BelongsTo
    {
        return $this->belongsTo(BarangayPersonnel::class, 'president_name_id', 'personnel_id');
    }

    /**
     * @return BelongsTo<BarangayPersonnel, $this>
     */
    public function kagawad(): BelongsTo
    {
        return $this->belongsTo(BarangayPersonnel::class, 'social_service_kagawad_id', 'personnel_id');
    }

    /**
     * @return HasMany<CertificateSoloParentChild, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(
            CertificateSoloParentChild::class,
            'certificate_solo_parent_id',
            'certificate_solo_parent_id',
        )->orderBy('certificate_solo_parent_child_id');
    }
}
