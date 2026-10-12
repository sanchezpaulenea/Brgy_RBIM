<?php

namespace App\Repositories\Interfaces;

use App\Models\Certificate\TypeOfCertificate;
use Illuminate\Database\Eloquent\Collection;

interface TypeOfCertificateRepositoryInterface
{
    /**
     * @return Collection<int, TypeOfCertificate>
     */
    public function all(): Collection;

    public function findById(int $typeOfCertificateId): ?TypeOfCertificate;
}
