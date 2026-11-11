<?php

namespace App\Models\Geographic;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'country';

    protected $primaryKey = 'country_id';

    public $timestamps = false;

    protected $fillable = [
        'country_name',
    ];

    public static function philippinesId(): int
    {
        $id = static::query()
            ->whereRaw('LOWER(country_name) = ?', ['philippines'])
            ->value('country_id');

        if ($id === null) {
            $id = static::query()->orderBy('country_id')->value('country_id');
        }

        return (int) $id;
    }
}
