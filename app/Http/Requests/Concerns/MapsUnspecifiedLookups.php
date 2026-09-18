<?php

namespace App\Http\Requests\Concerns;

trait MapsUnspecifiedLookups
{
    /**
     * Optional lookup selects post an empty string when the encoder leaves them
     * blank. The columns are INT NOT NULL behind a foreign key, so a blank
     * answer is recorded as the lookup table's "Not Applicable" row at id 0.
     *
     * @param  list<string>  $fields
     */
    protected function mergeUnspecifiedLookups(array $fields, bool $fillMissing = false, int $unspecifiedId = 0): void
    {
        $merge = [];

        foreach ($fields as $field) {
            if (! $this->exists($field)) {
                if ($fillMissing) {
                    $merge[$field] = $unspecifiedId;
                }

                continue;
            }

            $value = $this->input($field);

            if ($value === null || $value === '') {
                $merge[$field] = $unspecifiedId;
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
