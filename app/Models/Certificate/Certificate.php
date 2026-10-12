<?php

namespace App\Models\Certificate;

use App\Models\ResidentManagement\Demographic\Resident;
use App\Models\UserManagement\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
