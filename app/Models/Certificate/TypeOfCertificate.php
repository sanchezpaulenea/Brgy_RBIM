<?php

namespace App\Models\Certificate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeOfCertificate extends Model
{
    protected $table = 'type_of_certificate';

    protected $primaryKey = 'type_of_certificate_id';

    public $timestamps = false;

    protected $fillable = [
        'type_of_certificate',
    ];

    public function getRouteKeyName(): string
    {
        return 'type_of_certificate_id';
    }

    /**
     * @return HasMany<Certificate, $this>
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'type_of_certificate_id', 'type_of_certificate_id');
    }
}
