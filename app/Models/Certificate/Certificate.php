<?php

namespace App\Models\Certificate;

use App\Models\BarangayPersonnel\BarangayPersonnel;
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
        'resident_marital_status_id',
        'resident_gender_id',
        'brgy_captain_id',
        'representative_name',
        'representative_relationship',
        'representative_pronouns',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'date_of_issue' => 'datetime',
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
     * Resident row used to read marital status. The live foreign key points at resident.
     *
     * @return BelongsTo<Resident, $this>
     */
    public function maritalStatusResident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_marital_status_id', 'resident_id');
    }

    /**
     * Resident row used to read gender. The live foreign key points at resident.
     *
     * @return BelongsTo<Resident, $this>
     */
    public function genderResident(): BelongsTo
    {
        return $this->belongsTo(Resident::class, 'resident_gender_id', 'resident_id');
    }

    /**
     * @return BelongsTo<BarangayPersonnel, $this>
     */
    public function captain(): BelongsTo
    {
        return $this->belongsTo(BarangayPersonnel::class, 'brgy_captain_id', 'personnel_id');
    }

    /**
     * @return HasOne<DeathCertificate, $this>
     */
    public function deathRecord(): HasOne
    {
        return $this->hasOne(DeathCertificate::class, 'certificate_id', 'certificate_id');
    }

    /**
     * @return HasOne<LowIncomeCertificate, $this>
     */
    public function lowIncome(): HasOne
    {
        return $this->hasOne(LowIncomeCertificate::class, 'certificate_id', 'certificate_id');
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
        return $this->hasMany(CertificateSoloParentChild::class, 'certificate_solo_parent_id', 'certificate_id')
            ->orderBy('certificate_solo_parent_child_id');
    }
}
