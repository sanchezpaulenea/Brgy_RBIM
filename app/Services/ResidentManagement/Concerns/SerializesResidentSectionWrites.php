<?php

namespace App\Services\ResidentManagement\Concerns;

use App\Support\IdenticalWriteLock;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

trait SerializesResidentSectionWrites
{
    /**
     * Each profiling section allows one record per resident. Two accounts
     * saving that section at the same time must not both insert. The named
     * lock is taken before the transaction so the second save sees the first
     * row, and the unique index rejects the insert if the check is ever skipped.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function withResidentLock(int $residentId, callable $callback): mixed
    {
        return IdenticalWriteLock::acquire('resident-section:'.$residentId, function () use ($residentId, $callback) {
            return DB::transaction(function () use ($residentId, $callback) {
                DB::table('resident')
                    ->where('resident_id', $residentId)
                    ->lockForUpdate()
                    ->first();

                try {
                    return $callback();
                } catch (UniqueConstraintViolationException) {
                    throw ValidationException::withMessages([
                        'record' => ['This record was already saved for this resident.'],
                    ]);
                }
            });
        });
    }
}
