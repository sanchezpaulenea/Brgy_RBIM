<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'system_setting';

    protected $primaryKey = 'setting_id';

    public $timestamps = false;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'data_type',
        'description',
    ];

    public function getRouteKeyName(): string
    {
        return 'setting_id';
    }

    /**
     * Cast the raw varchar value to the correct PHP type based on `data_type`.
     */
    public function getTypedValue(): mixed
    {
        return match ($this->data_type) {
            'int' => (int) $this->setting_value,
            'bool' => filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN),
            default => $this->setting_value,
        };
    }
}
