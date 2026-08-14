<?php

namespace App\Services\Lookup;

use App\Enums\LookupType;
use App\Repositories\Interfaces\Lookup\LookupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LookupService
{
    public function __construct(
        protected LookupRepositoryInterface $lookupRepository
    ) {}

    /**
     * @return Collection<int, Model>
     */
    public function getAll(string $type): Collection
    {
        return $this->lookupRepository->getAll(LookupType::fromSlug($type));
    }

    public function create(string $type, array $attributes): Model
    {
        $lookupType = LookupType::fromSlug($type);

        if (! $lookupType->isWritable()) {
            throw new AccessDeniedHttpException('This lookup type is read-only.');
        }

        return $this->lookupRepository->create($lookupType, $attributes);
    }

    public function delete(string $type, int $id): void
    {
        $lookupType = LookupType::fromSlug($type);

        if (! $lookupType->isWritable()) {
            throw new AccessDeniedHttpException('This lookup type is read-only.');
        }

        try {
            $this->lookupRepository->delete($lookupType, $id);
        } catch (QueryException $exception) {
            if ($exception->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'id' => ['This position cannot be deleted because it is assigned to personnel records.'],
                ]);
            }

            throw $exception;
        }
    }
}