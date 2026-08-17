<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('system_setting')->upsert([
            ['setting_id' => 1,  'setting_key' => 'city_name',                'setting_value' => 'Baguio City',                                      'data_type' => 'string', 'description' => 'Official City Name'],
            ['setting_id' => 2,  'setting_key' => 'barangay_name',            'setting_value' => 'Barangay Happy Hallow',                             'data_type' => 'string', 'description' => 'Official Barangay Name'],
            ['setting_id' => 3,  'setting_key' => 'barangay_code',            'setting_value' => '1430300006',                                        'data_type' => 'string', 'description' => 'Philippine Standard Geographic Code'],
            ['setting_id' => 4,  'setting_key' => 'barangay_address',         'setting_value' => 'Barangay Happy Hallow, Baguio City, Benguet, ',     'data_type' => 'string', 'description' => 'Complete Address'],
            ['setting_id' => 5,  'setting_key' => 'barangay_contact_no',      'setting_value' => '09123456789',                                       'data_type' => 'string', 'description' => 'Contact Number'],
            ['setting_id' => 6,  'setting_key' => 'barangay_email',           'setting_value' => 'brgyhappyhallow@gmail.com',                         'data_type' => 'string', 'description' => 'Official Email Address'],
            ['setting_id' => 7,  'setting_key' => 'default_password',         'setting_value' => 'Temp12345',                                         'data_type' => 'string', 'description' => 'Default Password'],
            ['setting_id' => 8,  'setting_key' => 'password_min_length',      'setting_value' => '8',                                                 'data_type' => 'int',    'description' => 'Minimum length of password'],
            ['setting_id' => 9,  'setting_key' => 'max_login_attempts',       'setting_value' => '5',                                                 'data_type' => 'int',    'description' => 'Maximum attempts of login'],
            ['setting_id' => 10, 'setting_key' => 'account_lockout_minutes',  'setting_value' => '10',                                                'data_type' => 'int',    'description' => 'Account lockout minutes duration'],
            ['setting_id' => 11, 'setting_key' => 'session_timeout_minutes',  'setting_value' => '30',                                                'data_type' => 'int',    'description' => 'Session timeout minutes when idle'],
            ['setting_id' => 12, 'setting_key' => 'audit_log_retention_days', 'setting_value' => '365',                                               'data_type' => 'int',    'description' => 'Audit log retention days'],
        ], ['setting_id'], ['setting_key', 'setting_value', 'data_type', 'description']);
    }
}
