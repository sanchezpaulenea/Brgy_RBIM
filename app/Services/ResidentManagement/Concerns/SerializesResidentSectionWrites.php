<?php

namespace App\Services\ResidentManagement\Concerns;

use Illuminate\Support\Facades\DB;

trait SerializesResidentSectionWrites
{
    /**
     * Each profiling section allows one record per resident, but that is
     * enforced in application code rather than by a unique index. Two requests
     * fired at once (a double-clicked Save) would both pass the "does a record
     * already exist" check and insert. Taking a row lock on the parent resident
     * forces the second request to wait until the first has committed, so its
     * duplicate check sees the new record.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function withResidentLock(int $residentId, callable $callback): mixed
    {
        return DB::transaction(function () use ($residentId, $callback) {
            DB::table('resident')
                ->where('resident_id', $residentId)
                ->lockForUpdate()
                ->first();

            return $callback();
        });
    }
}
