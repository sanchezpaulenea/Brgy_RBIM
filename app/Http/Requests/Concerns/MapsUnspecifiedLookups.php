<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

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

    /**
     * @return array<int, mixed>
     */
    protected function unspecifiedLookupRule(string $table, string $column, ?string $inputKey = null): array
    {
        $key = $inputKey ?? $column;

        return [
            'nullable',
            'integer',
            Rule::when(
                fn () => (int) $this->input($key) > 0,
                [Rule::exists($table, $column)],
            ),
        ];
    }
}
