<?php

namespace App\Repositories;

use App\Models\Certificate\TypeOfCertificate;
use App\Repositories\Interfaces\TypeOfCertificateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TypeOfCertificateRepository implements TypeOfCertificateRepositoryInterface
{
    public function all(): Collection
    {
        return TypeOfCertificate::query()
            ->orderBy('type_of_certificate_id')
            ->get();
    }

    public function findById(int $typeOfCertificateId): ?TypeOfCertificate
    {
        return TypeOfCertificate::query()
            ->where('type_of_certificate_id', $typeOfCertificateId)
            ->first();
    }
}
