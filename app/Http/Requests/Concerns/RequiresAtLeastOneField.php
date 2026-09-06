<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Validator;

trait RequiresAtLeastOneField
{
    /**
     * @param  list<string>  $fields
     */
    protected function requireAtLeastOne(Validator $validator, array $fields, string $errorKey, string $message): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        foreach ($fields as $field) {
            if ($this->exists($field)) {
                return;
            }
        }

        $validator->errors()->add($errorKey, $message);
    }
}
