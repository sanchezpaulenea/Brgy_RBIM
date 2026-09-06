<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Str;

trait TitleCasesAttributes
{
    protected function titleCaseValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $formatted = Str::of($value)->squish()->title()->toString();

        return $formatted === '' ? null : $formatted;
    }

    /**
     * @param  list<string>  $fields
     */
    protected function mergeTitleCased(array $fields): void
    {
        $merge = [];

        foreach ($fields as $field) {
            if ($this->exists($field)) {
                $merge[$field] = $this->titleCaseValue($this->input($field));
            }
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
