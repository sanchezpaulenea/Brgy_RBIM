<?php

namespace App\Models\Lookup;

/**
 * Policy marker for lookup table authorization.
 * The `type` slug maps to a concrete lookup table via LookupRepository.
 */
class Lookup
{
    public function __construct(
        public readonly string $type,
    ) {}
}
