<?php

namespace App\Services\Lookups;

use App\Enums\LookupType;
use App\Repositories\Interfaces\Lookups\LookupRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LookupService
{
    public function __construct(
        protected LookupRepositoryInterface $lookupRepository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(LookupType $type): array
    {
        if (! $type->isReadOnlyReference()) {
            throw new NotFoundHttpException("Lookup type [{$type->value}] is not supported.");
        }

        return $this->lookupRepository
            ->all($type)
            ->map(fn (Model $model) => $type->format($model))
            ->values()
            ->all();
    }
}
