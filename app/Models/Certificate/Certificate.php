<?php

namespace App\Models\Certificate;

use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Certificate extends Model
{
    protected $table = 'certificate';

    protected $primaryKey = 'certificate_id';

    public $timestamps = false;

    protected $fillable = [
        'resident_id',
        'date_of_issue',
        'purpose_of_request',
        'type_of_certificate_id',
        'issuer_id',
        'representative_name',
        'representative_relationship',
        'date_of_death',
        'include_income_amount',
        'stated_income_amount',
        'place_of_death',
        'cause_of_death',
        'ethnic_group',
        'resident_since_year',
        'stated_civil_status',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_issue' => 'datetime',
        'date_of_death' => 'date',
        'include_income_amount' => 'boolean',
        'stated_income_amount' => 'decimal:2',
        'resident_since_year' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'certificate_id';
    }

    /**
     * @return BelongsTo<Resident, $this>
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_id', 'resident_id');
    }

    /**
     * @return BelongsTo<TypeOfCertificate, $this>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(
            TypeOfCertificate::class,
            'type_of_certificate_id',
            'type_of_certificate_id',
        );
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issuer_id', 'user_id');
    }

    /**
     * @return HasOne<CertificateSoloParent, $this>
     */
    public function soloParent(): HasOne
    {
        return $this->hasOne(CertificateSoloParent::class, 'certificate_id', 'certificate_id');
    }

    /**
     * @return HasMany<CertificateSoloParentChild, $this>
     */
    public function soloParentChildren(): HasMany
    {
        return $this->hasMany(CertificateSoloParentChild::class, 'certificate_id', 'certificate_id')
            ->orderBy('certificate_solo_parent_child_id');
    }
}
