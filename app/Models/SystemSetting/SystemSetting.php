<?php

namespace App\Models\SystemSetting;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
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

    /**
     * Retrieve a typed setting value by key.
     * Casts the raw varchar value to the correct PHP type based on `data_type`.
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
